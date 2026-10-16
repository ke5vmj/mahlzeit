<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Users;
use App\Models\UserGoals;

class GoalsController extends ControllerBase {

    /**
     * Renders the user's goals and metabolic settings dashboard.
     *
     * This method performs several biometric calculations:
     * 1. Age Calculation: Derived from the user's Date of Birth.
     * 2. BMR (Basal Metabolic Rate): Calculated via the Mifflin-St Jeor Equation.
     * 3. TDEE (Total Daily Energy Expenditure): BMR multiplied by the user's activity level.
     * 4. Goal Offsets: Re-calculates and snaps the calorie deficit/surplus to the
     *    nearest 250kcal increment to align with standard UI presets.
     *
     * If the user has not completed their profile (missing DOB), they are
     * redirected to the onboarding process.
     *
     * @return \Phalcon\Http\Response|void Redirects to onboarding if profile is incomplete,
     *                                     otherwise populates the goal-setting view.
     */
    public function indexAction() {
        $userId = $this->getCurrentUserId();
        $user = Users::findFirst($userId);

        if(!$user->getDob()) {
            return $this->response->redirect('/onboarding');
        }

        $goal = UserGoals::findFirst([
            'conditions' => 'user_id = :user_id:',
            'bind'       => ['user_id' => $userId]
        ]);

        // 1. Calculate Age from DOB
        $dob = new \DateTime($user->getDob());
        $now = new \DateTime();
        $age = $now->diff($dob)->y;

        $weightKg = (float) $user->getCurrentWeightKg();
        $heightCm = (float) $user->getHeightCm();
        $gender   = $user->getGender();

        // 2. Mifflin-St Jeor BMR Calculation
        $bmr = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age);
        $bmr = ($gender === 'M') ? $bmr + 5 : $bmr - 161;

        // 3. Calculate Maintenance Calories (TDEE)
        $tdee = (int) round($bmr * (float) $user->getActivityLevel());

        // 4. Pass stats to the view
        $this->view->tdee = $tdee;

        if ($goal) {
            $this->view->currentGoal  = $goal->getDailyCalorieTarget();
            $this->view->targetWeight = round($goal->getTargetWeightKg() * 2.20462, 1);

            // 1. Get the raw reconverted number (e.g., -496)
            $rawOffset = ($goal->getWeeklyGoalRateKg() / 0.453592) * 500;

            // 2. Snap it to the nearest 250 so it perfectly matches the dropdown!
            $this->view->rateOffset = (int) (round($rawOffset / 250) * 250);

            $this->view->proteinPct = $goal->getMacroProteinPct();
            $this->view->carbsPct   = $goal->getMacroCarbsPct();
            $this->view->fatPct     = $goal->getMacroFatPct();

        } else {
            // Defaults if they haven't set a goal yet
            $this->view->currentGoal  = $tdee;
            $this->view->targetWeight = '';
            $this->view->rateOffset   = 0;

            // Default Macros (Standard balanced diet)
            $this->view->proteinPct = 30;
            $this->view->carbsPct   = 35;
            $this->view->fatPct     = 35;
        }
    }

    /**
     * Persists the user's metabolic goals and macro-nutrient distribution.
     *
     * This method processes the "Goal Setting" form by:
     * 1. Validating that macro-nutrient percentages total exactly 100%.
     * 2. Calculating the daily calorie target based on TDEE and the selected rate offset.
     * 3. Enforcing a 1200 kcal "safety floor" to prevent dangerously low intake targets.
     * 4. Converting imperial inputs (Lbs, Calorie Offsets) back to metric units (Kg)
     *    for standardized database storage.
     * 5. Performing an upsert (update or create) on the UserGoals model.
     *
     * @return \Phalcon\Http\Response Redirects to the diary on success or
     *                                 back to the goals form on validation failure.
     */
    public function saveAction() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('/goals');
        }

        $userId = $this->getCurrentUserId();

        // Grab the base stats
        $tdee            = $this->request->getPost('tdee', 'int');
        $rateOffset      = $this->request->getPost('rate_offset', 'int');
        $targetWeightLbs = $this->request->getPost('target_weight_lbs', 'float');

        // Grab the Macros
        $proteinPct = $this->request->getPost('macro_protein_pct', 'int');
        $carbsPct   = $this->request->getPost('macro_carbs_pct', 'int');
        $fatPct     = $this->request->getPost('macro_fat_pct', 'int');

        // SAFETY CHECK: Ensure macros equal exactly 100%
        if (($proteinPct + $carbsPct + $fatPct) !== 100) {
            $this->flashSession->error("Your macro percentages must equal exactly 100%. Please adjust them and try again.");
            return $this->response->redirect('/goals');
        }

        // Calculate targets
        $dailyTarget = $tdee + $rateOffset;
        if ($dailyTarget < 1200) {
            $dailyTarget = 1200; // Safety floor
        }

        // Convert calorie offset back to kg/week for the database
        // +/- 500 cals = +/- 1 lb = +/- 0.453592 kg
        $weeklyRateKg = ($rateOffset / 500) * 0.453592;

        $targetWeightKg = $targetWeightLbs / 2.20462;

        $goal = UserGoals::findFirst([
            'conditions' => 'user_id = :user_id:',
            'bind'       => ['user_id' => $userId]
        ]);

        if (!$goal) {
            $goal = new UserGoals();
            $goal->setUserId($userId);
        }

        $goal->assign([
            'daily_calorie_target' => $dailyTarget,
            'weekly_goal_rate_kg'  => round($weeklyRateKg, 2),
            'target_weight_kg'     => round($targetWeightKg, 2),
            // Save the new macros!
            'macro_protein_pct'    => $proteinPct,
            'macro_carbs_pct'      => $carbsPct,
            'macro_fat_pct'        => $fatPct
        ]);

        if ($goal->save()) {
            $this->flashSession->success("Goals and Macros updated successfully!");
            return $this->response->redirect('/diary');
        }

        $this->flashSession->error("Failed to update goals.");
        return $this->response->redirect('/goals');
    }
}