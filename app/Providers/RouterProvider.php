<?php
declare(strict_types=1);

namespace App\Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Mvc\Router;

class RouterProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->setShared('router', function () {
            $router = new Router(false);
            
            // Explicitly load the routes file
            $routesFile = APP_PATH . '/Routes/web.php';
            
            if (file_exists($routesFile)) {
                // Scope closure to protect global namespace, 
                // passing $router explicitly
                (function($router) use ($routesFile) {
                    require $routesFile;
                })($router);
            }

            return $router;
        });
    }
}