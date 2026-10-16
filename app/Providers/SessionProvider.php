<?php
declare(strict_types=1);

namespace App\Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;

class SessionProvider implements ServiceProviderInterface {

    public function register(DiInterface $di): void {
        $di->setShared('session', function () use ($di) {
            $session = new Manager();
            $request = $di->getShared('request');

            $adapter = new Stream([
                'savePath'        => sys_get_temp_dir(),
                'prefix'          => 'sess_',
                'lifetime'        => 86400 * 30,
                'cookie_lifetime' => 86400 * 30,
                'cookie_secure'   => $request->isSecure(),
                'cookie_httponly' => true,
            ]);

            $session->setAdapter($adapter);
            $session->start();

            return $session;
        });
    }
}