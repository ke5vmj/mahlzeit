<?php
declare(strict_types=1);

namespace App\Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Mvc\Url;

class UrlProvider implements ServiceProviderInterface {
    public function register(DiInterface $di): void {
        $di->setShared('url', function () {
            $config = $this->getShared('config');

            $url = new Url();
            $url->setBaseUri($config->application->baseUri);
            return $url;
        });
    }
}