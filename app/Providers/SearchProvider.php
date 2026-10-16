<?php
declare(strict_types=1);

namespace App\Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use App\Search\Adapters\MeilisearchAdapter;

class SearchProvider implements ServiceProviderInterface {

    public function register(DiInterface $di): void {
        $di->setShared('search', function () {
            $config = [
                'endpoint' => [
                    'localhost' => [
                        // Assuming you add 'meilisearch' to your docker-compose.yml
                        'host' => 'meilisearch',
                        'port' => 7700,
                        'key'  => getenv('MEILI_MASTER_KEY')
                    ]
                ]
            ];

            return new MeilisearchAdapter($config);
        });
    }
}