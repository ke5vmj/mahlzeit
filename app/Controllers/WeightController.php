<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\WeightLogs;

class WeightController extends ControllerBase
{
    public function addAction(string $date) {
        $this->view->date = $date;

        $existingLog = WeightLogs::findFirst([
            'conditions' => 'user_id = :user_id: AND date = :date:',
            'bind'       => [
                'user_id' => $this->getCurrentUserId(),
                'date'    => $date
            ]
        ]);

        // Convert KG from DB to LBS for the form
        if ($existingLog) {
            $lbs = $existingLog->getWeightKg() * 2.20462;
            $this->view->currentWeight = round($lbs, 1);
        } else {
            $this->view->currentWeight = '';
        }
    }

    public function logAction() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('/diary');
        }

        $date      = $this->request->getPost('date', 'string');
        $weightLbs = $this->request->getPost('weight_lbs', 'float');
        $userId    = $this->getCurrentUserId();

        // Convert LBS from form to KG for the DB
        $weightKg = $weightLbs / 2.20462;

        $log = WeightLogs::findFirst([
            'conditions' => 'user_id = :user_id: AND date = :date:',
            'bind'       => [
                'user_id' => $userId,
                'date'    => $date
            ]
        ]);

        if (!$log) {
            $log = new WeightLogs();
            $log->setUserId($userId);
            $log->setDate($date);
        }

        // Save the precise KG value
        $log->setWeightKg(round($weightKg, 2));
        $log->save();

        return $this->response->redirect("/diary/{$date}");
    }
}