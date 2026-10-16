<?php
declare(strict_types=1);

namespace App\Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Mvc\Dispatcher;

class DispatcherProvider implements ServiceProviderInterface {
    public function register(DiInterface $di): void {
        $di->setShared('dispatcher', function () {
            $dispatcher = new Dispatcher();
            
            // This tells Phalcon to automatically prepend "App\Controllers\" 
            // to any controller found in the router.
            $dispatcher->setDefaultNamespace('App\Controllers');

            return $dispatcher;
        });
    }
}