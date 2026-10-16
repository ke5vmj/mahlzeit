<?php
declare(strict_types=1);

defined('BASE_PATH') || define('BASE_PATH', dirname(__DIR__));
defined('APP_PATH')  || define('APP_PATH', BASE_PATH);

use Phalcon\Config\Config;

return new Config([
    'application' => [
        'appDir'         => APP_PATH . '/',
        'controllersDir' => APP_PATH . '/Controllers/',
        'modelsDir'      => APP_PATH . '/Models/',
        'migrationsDir'  => APP_PATH . '/Migrations/',
        'viewsDir'       => APP_PATH . '/views/',
        'cacheDir'       => BASE_PATH . '/cache/',
        'baseUri'        => '/',
        // NEW: Toggle for DB Profiling. Defaults to false if not set in environment.
        'profileDb'      => filter_var(getenv('PROFILE_DB') ?? false, FILTER_VALIDATE_BOOLEAN),
        'cryptKey'       => getenv('KEY'),
    ],
    'database' => [
        'adapter'  => getenv('DB_ADAPTER') ?? 'Mysql',
        'host'     => getenv('DB_HOST') ?? 'localhost',
        'username' => getenv('MYSQL_USER') ?? 'root',
        'password' => getenv('MYSQL_PASSWORD') ?? '',
        'dbname'   => getenv('MYSQL_DATABASE') ?? 'test',
        'charset'  => getenv('DB_CHARSET') ?? 'utf8',
    ],
]);