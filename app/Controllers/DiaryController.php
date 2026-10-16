<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\DiaryEntries;
use App\Models\MealTypes;
use App\Models\Foods;

class DiaryController extends ControllerBase {

    /**
     * Display the user's daily diary dashboard.
     *
     * This method aggregates all nutritional and physical activity data for a specific date,
     * including:
     * - Categorizing food entries into meal types (Breakfast, Lunch, etc.).
     * - Calculating cumulative macro-nutrients (Protein, Carbs, Fats) and Calories.
     * - Retrieving exercise logs and total calories burned.
     * - Fetching and converting weight logs (KG to LBS).
     * - Comparing actual intake against user-defined or default calorie and macro goals.
     *
     * @param string|null $date The date to display in 'Y-m-d' format. Defaults to current date.
     *
     * @return void Sets multiple variables to $this->view including:
     *              - meals: Array of meal categories with nested food entries.
     *              - totals: Calculated daily macro and calorie intake.
     *              - exercises: List of activities and calories burned.
     *              - budget: Calorie target for the day.
     *              - macroTargets: Gram-based targets for protein, carbs, and fat.
     *              - loggedWeight: User's weight for the day in LBS.
     */
    public function indexAction(?string $date = null) {
        // Default to today if no date is in the URL
        if (!$date) {
            $date = date('Y-m-d');
        }

        $userId = $this->getCurrentUserId();

        $mealTypes = MealTypes::find([
            'conditions' => 'user_id = :user_id:',
            'bind'       => ['user_id' => $userId],
            'order'      => 'sort_order ASC'
        ]);

        // Fetch all diary entries for this user on this date
        $entries = DiaryEntries::find([
            'conditions' => 'user_id = :user_id: AND date = :date:',
            'bind'       => [
                'user_id' => $userId,
                'date'    => $date
            ]
        ]);

        // Initialize our display arrays and daily totals
        $meals = [];
        foreach ($mealTypes as $mealType) {
            $meals[$mealType->id] = ['name' => $mealType->name, 'entries' => [], 'calories' => 0];
        }
        $dailyTotals = ['calories' => 0, 'protein' => 0, 'carbs' => 0, 'fat' => 0];

        // Process the entries
        foreach ($entries as $entry) {
            $foodId = $entry->getFoodId();
            if (!$foodId) continue; // Skip if food was deleted

            $qty = (float) $entry->getQuantity();

            $food = Foods::findFirst($foodId);

            // Calculate the exact macros consumed
            $consumedCalories = (int) round($food->getCalories() * $qty);
            $consumedProtein  = $food->getProteinG() * $qty;
            $consumedCarbs    = $food->getCarbsG() * $qty;
            $consumedFat      = $food->getFatG() * $qty;

            // Add to the specific meal bucket
            $mealId = $entry->getMealTypeId();
            if (isset($meals[$mealId])) {
                $meals[$mealId]['entries'][] = [
                    'entry_id'     => $entry->getId(),
                    'food_name'    => $food->getName(),
                    'brand'        => $food->getBrand(),
                    'serving_text' => ($food->getServingSize() * $qty) . ' ' . $food->getServingUnit(),
                    'calories'     => $consumedCalories
                ];
                $meals[$mealId]['calories'] += $consumedCalories;
            }

            // Add to daily totals
            $dailyTotals['calories'] += $consumedCalories;
            $dailyTotals['protein'] += $consumedProtein;
            $dailyTotals['carbs'] += $consumedCarbs;
            $dailyTotals['fat'] += $consumedFat;
        }

        $exerciseLogs = \App\Models\ExerciseLogs::find([
            'conditions' => 'user_id = :user_id: AND date = :date:',
            'bind'       => [
                'user_id' => $userId,
                'date'    => $date
            ]
        ]);

        // ---- NEW WEIGHT LOGIC ---- //
        $weightLog = \App\Models\WeightLogs::findFirst([
            'conditions' => 'user_id = :user_id: AND date = :date:',
            'bind'       => [
                'user_id' => $userId,
                'date'    => $date
            ]
        ]);

        if ($weightLog) {
            // Convert KG to LBS for the UI
            $this->view->loggedWeight = round($weightLog->getWeightKg() * 2.20462, 1);
        } else {
            $this->view->loggedWeight = null;
        }

        // Fetch the last 5 weight logs up to the currently viewed date
        $recentWeightLogs = \App\Models\WeightLogs::find([
            'conditions' => 'user_id = :user_id: AND date <= :date:',
            'bind'       => [
                'user_id' => $userId,
                'date'    => $date
            ],
            'order'      => 'date DESC',
            'limit'      => 5
        ]);

        $recentWeights = [];
        foreach ($recentWeightLogs as $log) {
            $recentWeights[] = [
                // Format the date so it looks nice in the UI (e.g., "Aug 09")
                'date_formatted' => date('M d', strtotime($log->getDate())),
                'raw_date'       => $log->getDate(),
                'weight_lbs'     => round($log->getWeightKg() * 2.20462, 1)
            ];
        }

        $this->view->recentWeights = $recentWeights;

        $exercises = [];
        $totalExerciseCalories = 0;

        foreach ($exerciseLogs as $log) {
            $activity = $log->getRelated('Activities');
            $activityName = $activity ? $activity->name : 'Unknown Activity';

            // Fallback to custom name if they typed one in
            if ($log->getCustomName()) {
                $activityName = $log->getCustomName();
            }

            $burned = (int) $log->getCaloriesBurned();
            $totalExerciseCalories += $burned;

            $exercises[] = [
                'log_id'          => $log->getId(),
                'activity_name'   => $activityName,
                'duration'        => $log->getDurationMinutes(),
                'calories_burned' => $burned
            ];
        }

        // Fetch the user's specific goals
        $userGoal = \App\Models\UserGoals::findFirst([
            'conditions' => 'user_id = :user_id:',
            'bind'       => ['user_id' => $userId]
        ]);

        if ($userGoal) {
            $budget = (int) $userGoal->getDailyCalorieTarget();
            $this->view->budget = $budget;
            $this->view->targetWeightLbs = $userGoal->getTargetWeightKg() ? round($userGoal->getTargetWeightKg() * 2.20462, 1) : 'Set a goal';

            // The Math: Calorie Budget * Percentage = Macro Calories. Then divide by cal/gram.
            $this->view->macroTargets = [
                'protein' => (int) round(($budget * ($userGoal->getMacroProteinPct() / 100)) / 4),
                'carbs'   => (int) round(($budget * ($userGoal->getMacroCarbsPct() / 100)) / 4),
                'fat'     => (int) round(($budget * ($userGoal->getMacroFatPct() / 100)) / 9),
            ];
        } else {
            // Safe defaults if they haven't saved goals yet
            $budget = 2000;
            $this->view->budget = $budget;
            $this->view->targetWeightLbs = 'Set a goal';
            $this->view->macroTargets = [
                'protein' => (int) round(($budget * 0.30) / 4),
                'carbs'   => (int) round(($budget * 0.35) / 4),
                'fat'     => (int) round(($budget * 0.35) / 9),
            ];
        }

        // Pass the new exercise data to the view
        $this->view->exercises = $exercises;
        $this->view->totalExerciseCalories = $totalExerciseCalories;

        // Pass everything to the view
        $this->view->date = $date;
        $this->view->meals = $meals;
        $this->view->totals = $dailyTotals;

        // Simple previous/next day calculation for navigation
        $this->view->prevDate = date('Y-m-d', strtotime($date . ' -1 day'));
        $this->view->nextDate = date('Y-m-d', strtotime($date . ' +1 day'));
    }

    /**
     * Handles the form submission for adding a new diary entry
     * 1. Ensure this is actually a POST request
     * 2. Grab the data from the form
     * 3. Get our authenticated user (currently defaults to 1)
     * 4. Create and save the entry
     * 5. Redirect the user back to their daily diary page
     *
     * @return \Phalcon\Http\ResponseInterface
     */
    public function logAction() {
        // 1. Ensure this is actually a POST request
        if (!$this->request->isPost()) {
            return $this->response->redirect('/diary');
        }

        // 2. Grab the data from the form
        $date       = $this->request->getPost('date', 'string');
        $mealTypeId = $this->request->getPost('meal_type_id', 'int');
        $foodId     = $this->request->getPost('food_id', 'int');
        $quantity   = $this->request->getPost('quantity', 'float', 1.0);

        // 3. Get our authenticated user (currently defaults to 1)
        $userId = $this->getCurrentUserId();

        // 4. Create and save the entry
        $entry = new DiaryEntries();
        $entry->assign([
            'user_id'      => $userId,
            'food_id'      => $foodId,
            'date'         => $date,
            'meal_type_id' => $mealTypeId,
            'quantity'     => $quantity
        ]);

        if ($entry->save()) {

            // ---- NEW: UPDATE RECENT FOODS TIMESTAMP ---- //
            $userId = $this->getCurrentUserId();
            $foodId = (int) $this->request->getPost('food_id');

            $favorite = \App\Models\UserFavorites::findFirst([
                'conditions' => 'user_id = :uid: AND food_id = :fid:',
                'bind'       => ['uid' => $userId, 'fid' => $foodId]
            ]);

            if (!$favorite) {
                $favorite = new \App\Models\UserFavorites();
                $favorite->user_id   = $userId;
                $favorite->food_id   = $foodId;
                $favorite->is_pinned = 0; // Don't pin it, just track it!
            }

            // Stamp it with the exact current time
            $favorite->last_eaten_at = date('Y-m-d H:i:s');
            $favorite->save();
            // -------------------------------------------- //

            // If the request came from our Javascript fetch(), return JSON
            if ($this->request->isAjax()) {
                $this->view->disable();
                $this->response->setContentType('application/json', 'UTF-8');
                return $this->response->setJsonContent([
                    'status' => 'success',
                    'message' => 'Logged!'
                ]);
            }

            $this->flashSession->success("Food added to your diary!");
            return $this->response->redirect("/diary/{$date}");
        }

        if ($this->request->isAjax()) {
            $this->view->disable();
            $this->response->setStatusCode(400, 'Bad Request');
            return $this->response->setJsonContent(['status' => 'error']);
        }

        // 5. Redirect the user back to their daily diary page
        return $this->response->redirect("/diary/{$date}");
    }

    /**
     * Deletes a specific food diary entry.
     *
     * Validates that the request is a POST submission and confirms the entry
     * exists and belongs to the authenticated user before removal. Upon
     * successful deletion, the user is redirected back to the diary view
     * for the date associated with the deleted entry.
     *
     * @param string $id The unique identifier of the diary entry to delete.
     *
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     *         Redirects to the specific diary date on success, or the
     *         general diary index on failure/unauthorized access.
     */
    public function deleteAction(string $id) {
        // 1. Ensure it's a POST request
        if (!$this->request->isPost()) {
            return $this->response->redirect('/diary');
        }

        $userId = $this->getCurrentUserId();

        // 2. Find the entry AND ensure it belongs to the current user
        $entry = \App\Models\DiaryEntries::findFirst([
            'conditions' => 'id = :id: AND user_id = :user_id:',
            'bind'       => [
                'id'      => (int) $id,
                'user_id' => $userId
            ]
        ]);

        if ($entry) {
            // Grab the date so we can redirect them back to the correct day
            $date = $entry->getDate();

            // Delete it!
            $entry->delete();

            return $this->response->redirect("/diary/{$date}");
        }

        // Fallback if the entry wasn't found
        return $this->response->redirect('/diary');
    }

    /**
     * Prepares the view to edit an existing diary entry (e.g., change quantity).
     *
     * @param string $id The unique identifier of the diary entry.
     */
    public function editAction(string $id) {
        $userId = $this->getCurrentUserId();

        $entry = \App\Models\DiaryEntries::findFirst([
            'conditions' => 'id = :id: AND user_id = :user_id:',
            'bind'       => [
                'id'      => (int) $id,
                'user_id' => $userId
            ]
        ]);

        if (!$entry) {
            $this->flashSession->error("Entry not found.");
            return $this->response->redirect('/diary');
        }

        // Pass the entry, its related Food model, and the date back to the view
        $this->view->entry = $entry;
        $this->view->food = $entry->getRelated('Foods');
        $this->view->date = $entry->getDate();
    }

    /**
     * Processes the update of a diary entry's quantity.
     *
     * @param string $id The unique identifier of the diary entry.
     */
    public function updateAction(string $id) {
        if (!$this->request->isPost()) {
            return $this->response->redirect('/diary');
        }

        $userId = $this->getCurrentUserId();

        $entry = \App\Models\DiaryEntries::findFirst([
            'conditions' => 'id = :id: AND user_id = :user_id:',
            'bind'       => [
                'id'      => (int) $id,
                'user_id' => $userId
            ]
        ]);

        if ($entry) {
            $newQuantity = $this->request->getPost('quantity', 'float');

            if ($newQuantity > 0) {
                $entry->setQuantity($newQuantity);

                if ($entry->save()) {
                    $this->flashSession->success("Food amount updated!");
                } else {
                    $this->flashSession->error("Failed to update the entry.");
                }
            } else {
                $this->flashSession->error("Quantity must be greater than zero.");
            }

            return $this->response->redirect("/diary/" . $entry->getDate());
        }

        return $this->response->redirect('/diary');
    }
}