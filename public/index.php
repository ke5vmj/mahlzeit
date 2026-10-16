<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

try {

    /**
     * Handle the request
     */
    $bootstrap = new \App\Bootstrap();
    
    echo $bootstrap->run();
} catch (\Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
