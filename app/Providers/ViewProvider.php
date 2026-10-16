<?php
declare(strict_types=1);

namespace App\Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;

class ViewProvider implements ServiceProviderInterface {
    public function register(DiInterface $di): void {
        $di->setShared('view', function () {
            $config = $this->getConfig();

            $view = new View();
            $view->setDI($this);
            $view->setViewsDir($config->application->viewsDir);
            $view->setLayoutsDir($config->application->viewsDir . 'layouts/');
            $view->setPartialsDir($config->application->viewsDir . 'partials/');

            // Set the default layout
            $view->setMainView('main');

            $view->registerEngines([
                '.volt' => function ($view) {
                    $config = $this->getConfig();

                    $volt = new VoltEngine($view, $this);

                    $volt->setOptions([
                        'path' => $config->application->cacheDir,
                        'separator' => '_'
                    ]);

                    return $volt;
                },
                '.phtml' => PhpEngine::class

            ]);

            return $view;
        });
    }
}