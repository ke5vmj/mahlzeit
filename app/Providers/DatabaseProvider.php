<?php
declare(strict_types=1);

namespace App\Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Db\Profiler;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;

class DatabaseProvider implements ServiceProviderInterface {

    public function register(DiInterface $di): void {
        // 1. Register the profiler service first
        $di->setShared('profiler', function () {
            return new Profiler();
        });

        // 2. Update your db service to use the DI and attach the profiler
        $di->setShared('db', function () use ($di) {
            $config = $this->getShared('config');

            $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
            $params = [
                'host'     => $config->database->host,
                'username' => $config->database->username,
                'password' => $config->database->password,
                'dbname'   => $config->database->dbname,
                'charset'  => $config->database->charset
            ];

            $connection = new $class($params);

            // ONLY attach the profiler if the config flag is TRUE
            if ($config->application->path('profileDb', false)) {
                $eventsManager = new \Phalcon\Events\Manager();
                $profiler      = $di->getShared('profiler');

                $eventsManager->attach(
                    'db',
                    function (\Phalcon\Events\Event $event, $connection) use ($profiler) {
                        if ($event->getType() === 'beforeQuery') {
                            $profiler->startProfile($connection->getSQLStatement());
                        }

                        if ($event->getType() === 'afterQuery') {
                            $profiler->stopProfile();
                        }
                    }
                );

                $connection->setEventsManager($eventsManager);
            }

            return $connection;
        });
    }
}