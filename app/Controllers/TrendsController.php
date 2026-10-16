<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\WeightLogs;
use App\Models\DiaryEntries;
use App\Models\ExerciseLogs;
use App\Models\UserGoals;
use App\Models\Users;

class TrendsController extends ControllerBase {

    /**
     * Aggregates and formats progress data for the 30-day reporting dashboard.
     *
     * This method prepares synchronized data sets for client-side charting:
     * 1. Weight Trends: Fetches logs from the last 30 days (LOCF logic).
     * 2. Caloric Intake: Summed by day via JOIN on Foods.
     * 3. Exercise Burn: Summed by day from ExerciseLogs.
     * 4. Benchmark Data: Retrieves user's calorie budget.
     * 5. Trajectory: Calculates weight changes based on Net Calories (Intake - Exercise).
     */
    public function indexAction() {
        $userId = $this->getCurrentUserId();
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));

        // 1. Fetch Weight Data
        $weights = WeightLogs::find([
            'conditions' => 'user_id = :uid: AND date >= :start:',
            'bind'       => ['uid' => $userId, 'start' => $thirtyDaysAgo],
            'order'      => 'date ASC'
        ]);

        $weightData = [];
        foreach ($weights as $w) {
            $weightData[$w->getDate()] = round($w->getWeightKg() * 2.20462, 1);
        }

        // 2. Fetch Calorie Data (Summed by Day via JOIN)
        $calorieData = [];
        $builder = $this->modelsManager->createBuilder()
            ->columns([
                'App\Models\DiaryEntries.date AS date',
                'SUM(App\Models\Foods.calories * App\Models\DiaryEntries.quantity) AS total_cals'
            ])
            ->from('App\Models\DiaryEntries')
            ->innerJoin('App\Models\Foods', 'App\Models\Foods.id = App\Models\DiaryEntries.food_id')
            ->where('App\Models\DiaryEntries.user_id = :uid:', ['uid' => $userId])
            ->andWhere('App\Models\DiaryEntries.date >= :start:', ['start' => $thirtyDaysAgo])
            ->groupBy('App\Models\DiaryEntries.date');

        $calorieResult = $builder->getQuery()->execute();
        foreach ($calorieResult as $row) {
            $calorieData[$row->date] = (int) $row->total_cals;
        }

        // ---------------------------------------------------------
        // Fetch Exercise Data (Summed by Day)
        // ---------------------------------------------------------
        $exerciseData = [];
        $exBuilder = $this->modelsManager->createBuilder()
            ->columns([
                'App\Models\ExerciseLogs.date AS date',
                'SUM(App\Models\ExerciseLogs.calories_burned) AS total_burned'
            ])
            ->from('App\Models\ExerciseLogs')
            ->where('App\Models\ExerciseLogs.user_id = :uid:', ['uid' => $userId])
            ->andWhere('App\Models\ExerciseLogs.date >= :start:', ['start' => $thirtyDaysAgo])
            ->groupBy('App\Models\ExerciseLogs.date');

        $exerciseResult = $exBuilder->getQuery()->execute();
        foreach ($exerciseResult as $row) {
            $exerciseData[$row->date] = (int) $row->total_burned;
        }

        // 3. Get the Baseline Weight
        $baselineWeight = WeightLogs::findFirst([
            'conditions' => 'user_id = :uid: AND date < :start:',
            'bind'       => ['uid' => $userId, 'start' => $thirtyDaysAgo],
            'order'      => 'date DESC'
        ]);
        $lastKnownWeight = $baselineWeight ? round($baselineWeight->getWeightKg() * 2.20462, 1) : null;

        // 4. Build the continuous 30-day arrays
        $labels = [];
        $finalWeights = [];
        $finalCalories = [];
        $finalBurned = []; // Track daily burn to align with intake

        for ($i = 30; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('M j', strtotime($d));

            if (isset($weightData[$d])) {
                $lastKnownWeight = $weightData[$d];
            }
            $finalWeights[] = $lastKnownWeight;
            $finalCalories[] = isset($calorieData[$d]) ? $calorieData[$d] : 0;
            $finalBurned[] = isset($exerciseData[$d]) ? $exerciseData[$d] : 0;
        }

        // 5. Fetch User Goal benchmark
        $userGoal = UserGoals::findFirst([
            'conditions' => 'user_id = :uid:',
            'bind'       => ['uid' => $userId]
        ]);
        $budget = $userGoal ? (int) $userGoal->getDailyCalorieTarget() : 2000;

        // 6. Calculate 30-Day Future Trajectory
        $user = Users::findFirst($userId);
        if ($user) {
            $dob = new \DateTime($user->getDob());
            $now = new \DateTime();
            $age = $now->diff($dob)->y;

            $currentWeightKg = $lastKnownWeight ? ($lastKnownWeight / 2.20462) : $user->getCurrentWeightKg();

            // Calculate BMR (Mifflin-St Jeor Equation)
            $bmr = (10 * $currentWeightKg) + (6.25 * $user->getHeightCm()) - (5 * $age);
            $gender = strtolower($user->getGender());
            $bmr += (str_starts_with($gender, 'm')) ? 5 : -161;

            // Calculate TDEE
            $tdee = $bmr * (float) $user->getActivityLevel();

            // ---------------------------------------------------------
            // Calculate Net Intake (Intake - Exercise)
            // ---------------------------------------------------------
            $netTrackedDays = [];
            foreach ($finalCalories as $index => $intake) {
                // Ignore 0-calorie intake days so we don't skew the average downward
                if ($intake > 0) {
                    $netTrackedDays[] = $intake - $finalBurned[$index];
                }
            }

            $avgNetIntake = count($netTrackedDays) > 0 ? array_sum($netTrackedDays) / count($netTrackedDays) : $tdee;

            // Calculate Daily Deficit/Surplus based on NET intake (3500 kcal ≈ 1 lb)
            $dailyDeficit = $avgNetIntake - $tdee;
            $dailyWeightChangeLbs = $dailyDeficit / 3500;

            // Generate 30-Day Projection Arrays
            $projLabels = [];
            $projWeights = [];
            $targetWeights = [];
            $targetWeightLbs = $userGoal ? round($userGoal->getTargetWeightKg() * 2.20462, 1) : null;
            $startingWeightLbs = $currentWeightKg * 2.20462;

            for ($i = 1; $i <= 30; $i++) {
                $projLabels[] = date('M j', strtotime("+$i days"));
                $projWeights[] = round($startingWeightLbs + ($dailyWeightChangeLbs * $i), 1);

                if ($targetWeightLbs) {
                    $targetWeights[] = $targetWeightLbs;
                }
            }

            // Generate summary text for the view
            $change = $dailyWeightChangeLbs * 30;
            $absChange = round(abs($change), 1);

            if ($change > 0.1) {
                $trendText = "gain {$absChange} lbs";
            } elseif ($change < -0.1) {
                $trendText = "lose {$absChange} lbs";
            } else {
                $trendText = "maintain your current weight";
            }

            $this->view->projLabels = json_encode($projLabels);
            $this->view->projWeights = json_encode($projWeights);
            $this->view->targetWeights = json_encode($targetWeights);
            $this->view->avgIntake = round($avgNetIntake); // Now passes the Net Intake to the view
            $this->view->tdee = round($tdee);
            $this->view->trendText = $trendText;
        }

        // Pass existing variables
        $this->view->chartLabels   = json_encode($labels);
        $this->view->chartWeights  = json_encode($finalWeights);
        $this->view->chartCalories = json_encode($finalCalories);

        // Optional: Pass the burned calories array to the view if you want to chart them as well
        $this->view->chartBurned   = json_encode($finalBurned);
        $this->view->calorieBudget = $budget;
    }
}