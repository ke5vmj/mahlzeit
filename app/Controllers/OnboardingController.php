<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Users;
use App\Models\WeightLogs;

class OnboardingController extends ControllerBase
{
    public function indexAction()
    {
        // Renders the view: app/views/onboarding/index.volt
    }

    public function saveAction()
    {
        if (!$this->request->isPost()) {
            return $this->response->redirect('/onboarding');
        }

        $userId = $this->getCurrentUserId();
        $user = Users::findFirst($userId);

        if (!$user) {
            // Safety fallback if the session somehow got orphaned
            $this->session->destroy();
            return $this->response->redirect('/login');
        }

        // 1. Grab the raw POST data
        $dob           = $this->request->getPost('dob', 'string');
        $gender        = $this->request->getPost('gender', 'string');
        $activityLevel = $this->request->getPost('activity_level', 'float');

        $heightFt      = $this->request->getPost('height_ft', 'int');
        $heightIn      = $this->request->getPost('height_in', 'int');
        $weightLbs     = $this->request->getPost('weight_lbs', 'float');

        // 2. Do the metric conversions for the database
        // Height: (Feet * 12 + Inches) * 2.54 = Centimeters
        $heightCm = (int) round((($heightFt * 12) + $heightIn) * 2.54);

        // Weight: Lbs / 2.20462 = Kilograms
        $weightKg = round($weightLbs / 2.20462, 2);

        // 3. Update the User's profile
        $user->assign([
            'dob'               => $dob,
            'gender'            => $gender,
            'height_cm'         => $heightCm,
            'current_weight_kg' => $weightKg,
            'activity_level'    => $activityLevel
        ]);

        if ($user->save()) {
            // 4. Create their very first Weight Log for today!
            $date = date('Y-m-d');

            // Check if they somehow already have a log for today to prevent duplicates
            $existingLog = WeightLogs::findFirst([
                'conditions' => 'user_id = :user_id: AND date = :date:',
                'bind'       => ['user_id' => $userId, 'date' => $date]
            ]);

            if (!$existingLog) {
                $weightLog = new WeightLogs();
                $weightLog->assign([
                    'user_id'   => $userId,
                    'date'      => $date,
                    'weight_kg' => $weightKg
                ]);
                $weightLog->save();
            }

            // Send them straight to the fully functional dashboard!
            $this->flashSession->success('Profile completed! Welcome to Mahlzeit.');
            return $this->response->redirect('/diary');
        }

        // If save fails, send them back
        $this->flashSession->error('There was an error saving your profile. Please try again.');
        return $this->response->redirect('/onboarding');
    }
}