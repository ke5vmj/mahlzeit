<?php
declare(strict_types=1);

namespace App\Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Encryption\Crypt;

class CryptProvider implements ServiceProviderInterface {

    public function register(DiInterface $di): void {
        $di->setShared('crypt', function () {
            $config = $this->getShared('config');

            $crypt = new Crypt();
            // MUST be a long, random, highly secure string. Never share this!
            $crypt->setKey($config->application->cryptKey);
            return $crypt;
        });
    }
}