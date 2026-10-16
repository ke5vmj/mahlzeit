<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Users;

class IndexController extends ControllerBase
{
    public function indexAction() {
        // 1. Disable the view engine. We don't need HTML here.
        $this->view->disable();

        // 2. Check if logged in
        if ($this->session->has('AUTH_USER_ID')) {

            $userId = (int) $this->session->get('AUTH_USER_ID');
            $user = Users::findFirst($userId);

            // 3. Safety Check: Did they skip or abort onboarding?
            if ($user && (!$user->getDob() || !$user->getHeightCm())) {
                return $this->response->redirect('/onboarding');
            }

            // Everything is good, send them to the tracker
            return $this->response->redirect('/diary');
        }

        // If not logged in, force them to the login screen
        return $this->response->redirect('/login');
    }
}