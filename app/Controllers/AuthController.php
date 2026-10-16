<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Users;

class AuthController extends ControllerBase
{
    public function registerAction() {
        // Just renders the app/views/auth/register.volt view
    }

    public function loginAction() {
        // Renders the app/views/auth/login.volt view
    }

    /**
     * Handles the user registration process and account initialization.
     *
     * This method performs the following critical tasks:
     * 1. Request Validation: Ensures the submission is a POST request and sanitizes inputs.
     * 2. Identity Check: Prevents duplicate account creation by verifying email uniqueness.
     * 3. Secure Persistence: Hashes the user's password using PASSWORD_DEFAULT and saves the record.
     * 4. Session Management: Automatically authenticates the user upon successful creation.
     * 5. Environment Setup: Populates the user's account with a default set of meal types
     *    (Breakfast, Lunch, Dinner, Snacks) to enable immediate use of the food diary.
     * 6. Workflow Hand-off: Redirects the user to the onboarding flow to collect biometric data.
     *
     * @return \Phalcon\Http\Response Redirects to onboarding on success,
     *                                 or back to registration on failure/duplicate.
     */
    public function doRegisterAction() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('/register');
        }

        $email    = $this->request->getPost('email', 'email');
        $password = $this->request->getPost('password', 'string');

        // Check if user already exists
        if (Users::findFirstByEmail($email)) {
            // In a real app, flash an error message here
            return $this->response->redirect('/register');
        }

        $this->db->begin(); // Start Transaction

        try {
            $user = new Users();
            $user->assign([
                'email'    => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            if (!$user->save()) {
                throw new \Exception("Failed to save user.");
            }

            $userId = $user->getId();

            $defaultMeals = [
                ['name' => 'Breakfast', 'order' => 1],
                ['name' => 'Lunch', 'order' => 2],
                ['name' => 'Dinner', 'order' => 3],
                ['name' => 'Snacks', 'order' => 4]
            ];

            foreach ($defaultMeals as $meal) {
                $mealType = new \App\Models\MealTypes();
                $mealType->assign([
                    'user_id'    => $userId,
                    'name'       => $meal['name'],
                    'sort_order' => $meal['order']
                ]);

                if (!$mealType->save()) {
                    throw new \Exception("Failed to save meal types.");
                }
            }

            $this->db->commit();

            $this->session->start();
            $this->session->set('AUTH_USER_ID', $userId);
            return $this->response->redirect('/onboarding');

        } catch (\Exception $e) {
            // Undo everything if anything fails
            $this->db->rollback();
            $this->flashSession->error('Registration failed: ' . $e->getMessage());
            return $this->response->redirect('/register');
        }
    }


    /**
     * Authenticates a user and manages the post-login redirection logic.
     *
     * This method handles the following:
     * 1. Request Filtering: Restricts access to POST submissions only.
     * 2. Identity Verification: Retrieves the user by email and utilizes
     *    `password_verify()` to compare the input against the stored hash.
     * 3. Session Persistence: Sets a secure session variable upon successful
     *    authentication.
     * 4. State-Based Routing: Evaluates the user's profile completeness (e.g., DOB
     *    and height). If data is missing, it routes them to onboarding; otherwise,
     *    it proceeds to the main diary.
     * 5. Error Handling: Provides feedback via flash messages for invalid
     *    credentials.
     *
     * @return \Phalcon\Http\Response Redirects to /diary, /onboarding, or /login.
     */
    public function doLoginAction() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('/login');
        }

        $email    = $this->request->getPost('email', 'email');
        $password = $this->request->getPost('password', 'string');

        $user = Users::findFirstByEmail($email);

        if ($user && password_verify($password, $user->getPassword())) {
            $this->session->start();
            $this->session->set('AUTH_USER_ID', $user->getId());

            // --- STATELESS REMEMBER EVERYONE LOGIC ---

            // Hash the user's existing password hash to create a unique fingerprint.
            // If they change their password later, this fingerprint will change.
            $passwordFingerprint = hash('sha256', $user->getPassword());

            // Combine ID and Fingerprint: "123|abc123def456..."
            $cookieData = $user->getId() . '|' . $passwordFingerprint;

            // Expire in 30 days
            $expire = time() + (30 * 86400);

            // Set the cookie (Phalcon encrypts this automatically if Crypt service is set)
            $isHttps = $this->request->isSecure();
            $this->cookies->set('remember_me', $cookieData, $expire, '/', $isHttps, '', true);

            // Routing
            if (!$user->getDob() || !$user->getHeightCm()) {
                return $this->response->redirect('/onboarding');
            }
            return $this->response->redirect('/diary');
        }

        $this->flashSession->error('Incorrect email or password. Please try again.');
        return $this->response->redirect('/login');
    }

    public function logoutAction() {
        // 1. Destroy the active session
        $this->session->destroy();

        // 2. Delete the remember_me cookie if it exists
        if ($this->cookies->has('remember_me')) {
            $cookie = $this->cookies->get('remember_me');
            $cookie->delete();
        }

        // 3. Redirect back to login or home
        $this->flashSession->success('You have been securely logged out.');
        return $this->response->redirect('/login');
    }
}