<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Activities;
use App\Models\ExerciseLogs;

class ExerciseController extends ControllerBase
{
    /**
     * Prepares the interface to log a specific exercise activity.
     *
     * Fetches the activity details from the database and sets up the view to
     * allow the user to enter log-specific data (e.g., duration) for a given date.
     * If the activity ID is invalid, it triggers an error message and redirects
     * the user back to the search interface.
     *
     * @param string $date       The date the exercise is being logged for (format: Y-m-d).
     * @param string $activityId The unique ID of the activity selected from the library.
     *
     * @return \Phalcon\Http\Response|void Redirects to search on failure, otherwise
     *                                     populates $this->view with 'date' and 'activity'.
     */
    public function addAction(string $date, string $activityId) {
        $this->view->date = $date;

        $activity = \App\Models\Activities::findFirst((int) $activityId);
        if (!$activity) {
            $this->flashSession->error("Activity not found.");
            return $this->response->redirect("/diary/{$date}/exercise/search");
        }

        $this->view->activity = $activity;
    }

    /**
     * Processes the submission of an exercise log entry.
     *
     * This method calculates calories burned using the MET formula:
     * (MET * Weight in KG * Duration in Hours). It attempts to use the user's
     * most recent weight log for accuracy, falling back to a default of 79.6kg
     * if no history exists.
     *
     * Logic flow:
     * 1. Validates POST request.
     * 2. Retrieves activity MET value and the user's historical weight.
     * 3. Calculates energy expenditure.
     * 4. Persists the new ExerciseLogs record.
     *
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     *         Redirects to the diary view for the logged date.
     */
    public function logAction() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('/diary');
        }

        $date            = $this->request->getPost('date', 'string');
        $activityId      = $this->request->getPost('activity_id', 'int');
        $durationMinutes = $this->request->getPost('duration_minutes', 'int');

        // Fetch the specific activity to get its MET value
        $activity = Activities::findFirst($activityId);

        if (!$activity) {
            return $this->response->redirect("/diary/{$date}");
        }

        $met = (float) $activity->met_value;

        // Fetch the user's most recent weight prior to or on this date
        $latestWeightLog = \App\Models\WeightLogs::findFirst([
            'conditions' => 'user_id = :user_id: AND date <= :date:',
            'bind'       => [
                'user_id' => $this->getCurrentUserId(),
                'date'    => $date
            ],
            'order'      => 'date DESC'
        ]);

        // Fallback to an average default (e.g. 79.6 kg) if they have NEVER logged a weight
        $weightKg = $latestWeightLog ? (float) $latestWeightLog->getWeightKg() : 79.6;

        $durationHours = $durationMinutes / 60;
        $caloriesBurned = (int) round($met * $weightKg * $durationHours);

        $log = new ExerciseLogs();
        $log->assign([
            'user_id'          => $this->getCurrentUserId(),
            'activity_id'      => $activityId,
            'date'             => $date,
            'duration_minutes' => $durationMinutes,
            'calories_burned'  => $caloriesBurned
        ]);

        $log->save();

        return $this->response->redirect("/diary/{$date}");
    }

    /**
     * Deletes a specific exercise log entry.
     *
     * This method enforces a POST-only request policy to prevent CSRF or
     * accidental deletions from search crawlers. It verifies the log exists
     * and is owned by the current user before performing the deletion.
     *
     * Upon success, the user is redirected back to the specific diary date
     * the exercise was logged on to maintain UI context.
     *
     * @param string $id The unique identifier of the exercise log record.
     *
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     *         Redirects to the relevant diary date or the diary index.
     */
    public function deleteAction(string $id) {
        // 1. Ensure it's a POST request to prevent accidental/crawled deletions
        if (!$this->request->isPost()) {
            return $this->response->redirect('/diary');
        }

        $userId = $this->getCurrentUserId();

        // 2. Find the log AND ensure it belongs to the current user
        $log = \App\Models\ExerciseLogs::findFirst([
            'conditions' => 'id = :id: AND user_id = :user_id:',
            'bind'       => [
                'id'      => (int) $id,
                'user_id' => $userId
            ]
        ]);

        if ($log) {
            // Grab the date so we can redirect them right back to the day they were viewing
            $date = $log->getDate();

            // Delete the record from MySQL
            $log->delete();

            return $this->response->redirect("/diary/{$date}");
        }

        // Fallback if the entry wasn't found
        return $this->response->redirect('/diary');
    }


    /**
     * Prepares the exercise search and selection interface.
     *
     * Fetches all available physical activities from the database, sorted
     * alphabetically by category and name. The activities are then grouped
     * by category into a multidimensional array to facilitate a structured
     * display (e.g., grouped lists or accordions) in the view.
     *
     * @param string $date The date the user is currently managing (Y-m-d),
     *                     carried through to maintain state during selection.
     *
     * @return void Sets 'date' and 'groupedActivities' to $this->view.
     */
    public function searchAction(string $date) {
        $this->view->date = $date;

        // 1. Fetch activities sorted by Category, then by Name
        $activities = \App\Models\Activities::find([
            'order' => 'category ASC, name ASC'
        ]);

        // 2. Group them into a new array
        $groupedActivities = [];
        foreach ($activities as $activity) {
            // Provide a fallback just in case an activity lacks a category
            $categoryName = $activity->category ? $activity->category : 'Other';
            $groupedActivities[$categoryName][] = $activity;
        }

        $this->view->groupedActivities = $groupedActivities;
    }
}