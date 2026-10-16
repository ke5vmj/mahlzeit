<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\MealTypes;
use App\Models\DiaryEntries;
use App\Models\Users;
use App\Models\UserGoals;

class SettingsController extends ControllerBase
{
    /**
     * Prepares the user profile and preferences interface.
     *
     * This method retrieves the user's current biometric data and customizable
     * meal categories. It performs a conversion of height data from centimeters
     * (stored in the database) to feet and inches to facilitate a user-friendly
     * imperial input display.
     *
     * @return void Sets 'meals', 'user', 'heightFt', and 'heightIn' to $this->view.
     */
    public function indexAction() {
        $userId = $this->getCurrentUserId();

        // 1. MEAL CATEGORIES
        $this->view->meals = MealTypes::find([
            'conditions' => 'user_id = :user_id:',
            'bind'       => ['user_id' => $userId],
            'order'      => 'sort_order ASC'
        ]);

        // 2. USER PROFILE
        $user = Users::findFirst($userId);
        $this->view->user = $user;

        // Convert Height CM back to Feet/Inches for the view
        $cm = $user->getHeightCm();
        if ($cm) {
            $inchesTotal = round($cm / 2.54);
            $this->view->heightFt = floor($inchesTotal / 12);
            $this->view->heightIn = $inchesTotal % 12;
        } else {
            $this->view->heightFt = '';
            $this->view->heightIn = '';
        }
    }

    /**
     * Adds a new custom meal category to the user's profile.
     *
     * This method manages the creation of a new meal type (e.g., "Second Breakfast")
     * by determining the next logical position in the display sequence. It:
     * 1. Validates the POST request.
     * 2. Identifies the current maximum 'sort_order' for the authenticated user.
     * 3. Appends the new category to the end of the list.
     * 4. Provides UI feedback via flash messages upon success or failure.
     *
     * @return \Phalcon\Http\Response Redirects back to the settings/profile page.
     */
    public function addMealAction() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('/settings');
        }

        $name   = $this->request->getPost('name', 'string');
        $userId = $this->getCurrentUserId();

        // Find the user's highest sort_order so we can append this new meal to the end of the list
        $lastMeal = MealTypes::findFirst([
            'conditions' => 'user_id = :user_id:',
            'bind'       => ['user_id' => $userId],
            'order'      => 'sort_order DESC'
        ]);

        $nextOrder = $lastMeal ? $lastMeal->sort_order + 1 : 1;

        $meal = new MealTypes();
        $meal->assign([
            'user_id'    => $userId,
            'name'       => $name,
            'sort_order' => $nextOrder
        ]);

        if ($meal->save()) {
            $this->flashSession->success("Added '{$name}' to your meal categories.");
        } else {
            $this->flashSession->error("Failed to add meal type.");
        }

        return $this->response->redirect('/settings');
    }

    /**
     * Deletes a user-defined meal category.
     *
     * This method performs a "Safe Delete" by verifying that no historical
     * diary entries are currently associated with the meal category. If
     * dependencies exist, the deletion is blocked to maintain data integrity.
     *
     * Logic flow:
     * 1. Validates the POST request and verifies ownership of the meal type.
     * 2. Counts existing diary entries linked to this specific meal category.
     * 3. If entries > 0: Blocks deletion and notifies the user of the conflict.
     * 4. If entries = 0: Executes the deletion and confirms success.
     *
     * @param string $id The unique identifier of the meal type to delete.
     *
     * @return \Phalcon\Http\Response Redirects back to the settings/profile page.
     */
    public function deleteMealAction(string $id) {
        if (!$this->request->isPost()) {
            return $this->response->redirect('/settings');
        }

        $userId = $this->getCurrentUserId();

        $meal = MealTypes::findFirst([
            'conditions' => 'id = :id: AND user_id = :user_id:',
            'bind'       => ['id' => (int) $id, 'user_id' => $userId]
        ]);

        if ($meal) {
            // SAFETY CHECK: Count how many diary entries use this meal type
            $entryCount = DiaryEntries::count([
                'conditions' => 'meal_type_id = :meal_id: AND user_id = :user_id:',
                'bind'       => ['meal_id' => $meal->getId(), 'user_id' => $userId]
            ]);

            if ($entryCount > 0) {
                // Block the deletion and warn the user
                $this->flashSession->error("Cannot delete '{$meal->getName()}' because you have foods logged under it.");
            } else {
                // Safe to delete!
                $meal->delete();
                $this->flashSession->success("Meal category deleted.");
            }
        }

        return $this->response->redirect('/settings');
    }

    /**
     * Updates the user's biological profile and triggers a goal recalculation.
     *
     * This method processes the core biometric data used for metabolic math.
     * It handles the following:
     * 1. Unit Conversion: Translates imperial height (feet/inches) from the UI
     *    into centimeters for standardized database storage.
     * 2. Profile Persistence: Updates the user's Date of Birth, Gender,
     *    Height, and Activity Level.
     * 3. Goal Synchronization: Automatically invokes the internal
     *    `recalculateUserGoals` method to refresh the user's calorie budget
     *    based on the newly provided biometric data.
     *
     * @return \Phalcon\Http\Response Redirects to the settings page with
     *                                 a success or error flash message.
     */
    public function updateProfileAction() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('/settings');
        }

        $userId = $this->getCurrentUserId();
        $user = Users::findFirst($userId);

        // 1. Grab new biological stats
        $dob           = $this->request->getPost('dob', 'string');
        $gender        = $this->request->getPost('gender', 'string');
        $activityLevel = $this->request->getPost('activity_level', 'float');

        // 2. Convert Height back to CM
        $heightFt = $this->request->getPost('height_ft', 'int');
        $heightIn = $this->request->getPost('height_in', 'int');
        $heightCm = (int) round((($heightFt * 12) + $heightIn) * 2.54);

        $user->assign([
            'dob'            => $dob,
            'gender'         => $gender,
            'height_cm'      => $heightCm,
            'activity_level' => $activityLevel
        ]);

        if ($user->save()) {
            // 3. RECALCULATE THEIR DAILY CALORIE GOAL
            $this->recalculateUserGoals($user);
            $this->flashSession->success("Profile updated successfully!");
        } else {
            $this->flashSession->error("Failed to update profile.");
        }

        return $this->response->redirect('/settings');
    }

    /**
     * Automatically updates a user's calorie budget based on updated biometrics.
     *
     * This internal utility synchronizes the user's daily calorie target whenever
     * key attributes (weight, height, age, or activity level) change. It maintains
     * the user's preferred weight-loss/gain pace while recalculating the
     * underlying metabolic math.
     *
     * Process:
     * 1. Re-calculates BMR using the Mifflin-St Jeor Equation.
     * 2. Calculates TDEE based on current activity multipliers.
     * 3. Re-applies the existing weekly pace offset (converted from Kg to Calories).
     * 4. Enforces the 1200 kcal health safety floor.
     * 5. Persists the updated daily target to the UserGoals model.
     *
     * @param Users $user The user entity whose goals need synchronization.
     *
     * @return void
     */
    private function recalculateUserGoals(Users $user) {
        $goal = UserGoals::findFirst([
            'conditions' => 'user_id = :uid:',
            'bind'       => ['uid' => $user->getId()]
        ]);

        if (!$goal) return; // If they haven't set a goal yet, skip this

        // Calc Age
        $dob = new \DateTime($user->getDob());
        $now = new \DateTime();
        $age = $now->diff($dob)->y;

        // BMR Math
        $weightKg = (float) $user->getCurrentWeightKg();
        $heightCm = (float) $user->getHeightCm();
        $bmr = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age);
        $bmr = ($user->getGender() === 'M') ? $bmr + 5 : $bmr - 161;

        // TDEE Math
        $tdee = (int) round($bmr * (float) $user->getActivityLevel());

        // Re-apply their saved weekly pace offset
        // (Convert stored kg/week back into calorie offset: +/- 0.453592 kg = +/- 500 cals)
        $rateOffset = ($goal->getWeeklyGoalRateKg() / 0.453592) * 500;

        $newTarget = $tdee + $rateOffset;
        if ($newTarget < 1200) $newTarget = 1200; // Safety floor

        // Save the new budget!
        $goal->setDailyCalorieTarget((int) round($newTarget));
        $goal->save();
    }
}