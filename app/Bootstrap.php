<?php
declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;
use App\Providers\DatabaseProvider;
use App\Providers\ViewProvider;
use App\Providers\FlashProvider;
use App\Providers\SessionProvider;
use App\Providers\UrlProvider;
use App\Providers\RouterProvider;
use App\Providers\DispatcherProvider;
use App\Providers\SearchProvider;

class Bootstrap {
    private $di;

    public function __construct() {
        $this->di = new FactoryDefault();
    }

    public function run(): string {
        $this->loadEnvironment();
        $this->loadConfig();
        $this->registerProviders();
        
        // Initialize the Application
        $application = new Application($this->di);

        // Handle the Request
        return $application->handle($_SERVER['REQUEST_URI'])->getContent();
    }

    private function loadEnvironment(): void {
        $dotenv = Dotenv::createImmutable(BASE_PATH);
        $dotenv->safeLoad();
    }

    private function loadConfig(): void {
        $this->di->setShared('config', function () {
            return include APP_PATH . '/config/config.php';
        });
    }

    private function registerProviders(): void {
        // This makes your app extensible. 
        // You can simply add/remove classes from this array.
        $providers = [
            DatabaseProvider::class,
            DispatcherProvider::class,
            ViewProvider::class,
            UrlProvider::class,
            SessionProvider::class,
            FlashProvider::class,
            RouterProvider::class,
            SearchProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->di->register(new $provider());
        }
    }
}