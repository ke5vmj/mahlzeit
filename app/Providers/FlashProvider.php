<?php
declare(strict_types=1);

namespace App\Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Html\Escaper;

class FlashProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void {
        $di->setShared('flashSession', function () {
            // 1. Instantiate the required HTML Escaper for XSS protection
            $escaper = new Escaper();

            // 2. Grab your existing session service from the Dependency Injector
            $session = $this->getShared('session');

            // 3. Pass both to the constructor
            $flash = new FlashSession($escaper, $session);

            // 4. Map the Bulma CSS classes using the modern method
            $flash->setCssClasses([
                'error'   => 'notification is-danger',
                'success' => 'notification is-success',
                'notice'  => 'notification is-info',
                'warning' => 'notification is-warning'
            ]);

            return $flash;
        });
    }
}