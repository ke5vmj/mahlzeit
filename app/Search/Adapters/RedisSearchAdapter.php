<?php
declare(strict_types=1);

namespace App\Search\Adapters;

use App\Search\SearchAdapterInterface;
use App\Models\Foods;

class RedisSearchAdapter implements SearchAdapterInterface {
    private \Redis $client;
    private string $indexName = 'idx:foods';

    public function __construct(array $config) {
        $this->client = new \Redis();
        $hostConfig = $config['endpoint']['localhost'];

        // We defer connecting to the connect() method per the interface
        $this->host = $hostConfig['host'];
        $this->port = $hostConfig['port'];
    }

    public function connect(): bool {
        try {
            return $this->client->connect($this->host, $this->port);
        } catch (\RedisException $e) {
            return false;
        }
    }

    public function indexFood(Foods $food, bool $commit = true): bool {
        try {
            // Redis keys usually follow a pattern like "entity:id"
            $key = "food:" . $food->getId();

            // We use '0' to represent global foods, making tag filtering easier later
            $userId = $food->getUserId() ? (string) $food->getUserId() : '0';

            // Store the data as a Redis Hash
            $this->client->hMSet($key, [
                'id'          => $food->getId(),
                'name'        => (string) $food->getName(),
                'brand'       => (string) $food->getBrand(),
                'ingredients' => (string) $food->getIngredients(),
                'calories'    => $food->getCalories(),
                'user_id'     => $userId
            ]);

            // RediSearch automatically indexes the hash the moment it's saved.
            // No explicit commit is needed in Redis, so we ignore the $commit flag.
            return true;
        } catch (\RedisException $e) {
            return false;
        }
    }

    public function commit(): bool {
        // Redis data is immediately available in RAM.
        // If you had pipeline operations, you would execute them here.
        return true;
    }

    public function searchFoods(string $query, ?int $userId = null, int $limit = 20, int $offset = 0): array {
        try {
            // 1. Sanitize the query for RediSearch
            $safeQuery = $this->escapeRedisValue($query);

            // 2. Build the query string
            // We want to search text fields, but restrict to Global (0) OR this User's ID
            $searchUserId = $userId ? $userId : 0;

            // Syntax: "SearchTerm (@user_id:{0 | 5})"
            $queryString = "{$safeQuery}* (@user_id:{0 | {$searchUserId}})";

            // 3. Execute FT.SEARCH
            $rawResults = $this->client->rawCommand(
                'FT.SEARCH',
                $this->indexName,
                $queryString,
                'LIMIT', $offset, $limit
            );

            // 4. Parse the bizarre array structure RediSearch returns
            // Format: [ TotalCount, Key1, [Field1, Val1...], Key2, [Field2, Val2...] ]
            $results = [];
            $totalCount = $rawResults[0];

            if ($totalCount > 0) {
                // Iterate through the results (skipping the count at index 0)
                // They come in pairs: the key name, then an array of the hash data
                for ($i = 1; $i < count($rawResults); $i += 2) {
                    $hashData = $rawResults[$i + 1];

                    // Convert the flat [key, val, key, val] array into an associative array
                    $foodData = [];
                    for ($j = 0; $j < count($hashData); $j += 2) {
                        $foodData[$hashData[$j]] = $hashData[$j + 1];
                    }

                    $results[] = [
                        'id'          => $foodData['id'] ?? null,
                        'name'        => $foodData['name'] ?? 'Unknown',
                        'brand'       => $foodData['brand'] ?? '',
                        'calories'    => $foodData['calories'] ?? 0,
                        'ingredients' => $foodData['ingredients'] ?? '',
                    ];
                }
            }

            return $results;
        } catch (\RedisException $e) {
            return [];
        }
    }

    public function setupIndex(): void {
        // You could programmatically run FT.CREATE here.
        // If your Redis index is already created via CLI, this can safely remain empty.
    }

    public function indexFoodsBatch(array $foods): bool {
        try {
            // Use a pipeline to send all commands to Redis in a single network request
            $pipe = $this->client->multi(\Redis::PIPELINE);

            foreach ($foods as $food) {
                $key = "food:" . $food->getId();
                $userId = $food->getUserId() ? (string) $food->getUserId() : '0';

                $pipe->hMSet($key, [
                    'id'          => $food->getId(),
                    'name'        => (string) $food->getName(),
                    'brand'       => (string) $food->getBrand(),
                    'ingredients' => (string) $food->getIngredients(),
                    'calories'    => $food->getCalories(),
                    'user_id'     => $userId
                ]);
            }

            // Execute the pipeline
            $pipe->exec();
            return true;
        } catch (\RedisException $e) {
            error_log("Redis batch indexing failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Escapes punctuation characters that RediSearch treats as syntax operators
     */
    private function escapeRedisValue(string $value): string {
        $pattern = '/([,.<>{}\[\]"\':;!@#$%^&*()\-+=~|\/\\\\])/';
        return preg_replace($pattern, '\\\\$1', $value);
    }
}