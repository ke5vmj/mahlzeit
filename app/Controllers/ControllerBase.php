<?php
declare(strict_types=1);

namespace App\Controllers;

use Phalcon\Mvc\Controller;
use App\Models\Users;

class ControllerBase extends Controller {

    /**
     * Executes before every single route in the application.
     */
    public function beforeExecuteRoute() {
        // Start the session (reads the session cookie, if any)
        $this->session->start();

        // 1. If the user is ALREADY logged in via the session, do nothing.
        if ($this->session->has('AUTH_USER_ID')) {
            return true;
        }

        // 2. If they are NOT in the session, check for the cookie.
        $this->autoLoginByCookie();
    }

    public function afterExecuteRoute() {
        $config = $this->di->getShared('config');

        // Only pass profiler data to the view if profiling is enabled
        if ($config->application->path('profileDb', false)) {
            if ($this->di->has('profiler')) {
                $profiler = $this->di->getShared('profiler');

                $this->view->setVar('dbProfiles', $profiler->getProfiles());
                $this->view->setVar('dbTotalQueries', $profiler->getNumberTotalStatements());
            }
        }
    }

    public function initialize() {
        // Define which controllers are public (no login required)
        $publicControllers = ['index', 'auth', 'onboarding'];

        $currentController = $this->dispatcher->getControllerName();

        // If they are trying to access a protected page (like Diary or Food)...
        if (!in_array($currentController, $publicControllers)) {
            // ...and they don't have a session...
            if (!$this->session->has('AUTH_USER_ID')) {
                // ...kick them back to the landing page!
                $this->response->redirect('/login');
                // Return false to stop the execution of the requested action
                return false;
            }

            $user = \App\Models\Users::findFirst($this->session->get('AUTH_USER_ID'));
            if ($user && (!$user->getDob() || !$user->getHeightCm())) {
                $this->response->redirect('/onboarding');
                return false;
            }
        }
    }

   public function onConstruct() {
        // Strip namespace from controller name to get view path
        // $controllerName = strtolower(
        //     str_replace(
        //         'Controller', 
        //         '', 
        //         (new \ReflectionClass($this))->getShortName()
        //     )
        // );

        $headerCollection = $this->assets->collection('headerCss');

        $headerCollection->addCss('https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css', false);
        $headerCollection->addCss('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', false);
        
        // $actionName = $this->dispatcher->getActionName();
        // $this->view->pick("$controllerName/$actionName");

        // 4. Add a custom generic style for app-wide tweaks
        // (Create this file in public/css/app.css later if you want custom overrides)
        $headerCollection->addCss('css/custom.css');
    }

    /**
     * Get the currently authenticated User's ID.
     * * @return int
     */
    protected function getCurrentUserId(): int {
        // We can now safely rely on the session!
        return (int) $this->session->get('AUTH_USER_ID');
    }

    /**
     * Validates the stateless cookie and restores the session.
     */
    protected function autoLoginByCookie(): bool {
        if ($this->cookies->has('remember_me')) {
            $cookie = $this->cookies->get('remember_me');
            $cookieData = $cookie->getValue();

            $parts = explode('|', $cookieData);

            if (count($parts) === 2) {
                list($userId, $cookieFingerprint) = $parts;

                $user = Users::findFirst((int)$userId);

                if ($user) {
                    $currentFingerprint = hash('sha256', $user->getPassword());

                    if (hash_equals($currentFingerprint, $cookieFingerprint)) {
                        // Success! Restore the session.
                        $this->session->start();
                        $this->session->set('AUTH_USER_ID', $user->getId());
                        return true;
                    }
                }
            }

            // The cookie is invalid, expired, or tampered with. Destroy it.
            $cookie->delete();
        }

        return false;
    }
}
