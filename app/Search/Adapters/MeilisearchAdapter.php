<?php
declare(strict_types=1);

namespace App\Search\Adapters;

use App\Search\SearchAdapterInterface;
use App\Models\Foods;
use Meilisearch\Client;
use Meilisearch\Exceptions\ApiException;

class MeilisearchAdapter implements SearchAdapterInterface {
    private Client $client;
    private string $indexName = 'foods';

    public function __construct(array $config) {
        $hostConfig = $config['endpoint']['localhost'];

        $host = $hostConfig['host'];
        // Ensure http:// prefix is present
        if (!str_starts_with($host, 'http')) {
            $host = 'http://' . $host;
        }
        $host = $host . ':' . $hostConfig['port'];

        $key = $hostConfig['key'] ?? null;

        $this->client = new Client($host, $key);
    }

    /**
     * Ensures the index is configured with the correct schema settings.
     */
    public function setupIndex(): void {
        try {
            $index = $this->client->index($this->indexName);

            try {
                // This will throw an ApiException if the index doesn't exist yet
                $currentFilters = $index->getFilterableAttributes();
            } catch (\Meilisearch\Exceptions\ApiException $e) {
                // 404 means the index is entirely missing.
                if ($e->httpStatus === 404) {
                    // Pushing settings to a non-existent index automatically creates it.
                    $index->updateFilterableAttributes(['user_id']);
                    return; // Setup is complete!
                }

                // If it's some other API error, throw it to the outer block
                throw $e;
            }

            // If the index DOES exist, check if user_id is already configured
            if (!in_array('user_id', $currentFilters)) {
                $index->updateFilterableAttributes(['user_id']);
            }

        } catch (\Exception $e) {
            error_log("Failed to setup Meilisearch index: " . $e->getMessage());
        }
    }

    public function connect(): bool {
        try {
            // Meilisearch has a native health check endpoint
            $health = $this->client->health();
            return $health['status'] === 'available';
        } catch (\Exception $e) {
            error_log("Meilisearch connection failed: " . $e->getMessage());
            return false;
        }
    }

    public function indexFood(Foods $food, bool $commit = true): bool {
        try {
            // Use '0' for global foods to maintain consistency with your filtering logic
            $userId = $food->getUserId() ? (int) $food->getUserId() : 0;

            $document = [
                'id'          => $food->getId(),
                'name'        => (string) $food->getName(),
                'brand'       => (string) $food->getBrand(),
                'ingredients' => (string) $food->getIngredients(),
                'calories'    => $food->getCalories(),
                'user_id'     => $userId
            ];

            // Meilisearch handles batching and indexing asynchronously
            $this->client->index($this->indexName)->addDocuments([$document], 'id');

            // Note: $commit is ignored here because Meilisearch handles commits automatically
            // via its internal task queue.
            return true;
        } catch (\Exception $e) {
            error_log("Meilisearch indexing failed: " . $e->getMessage());
            return false;
        }
    }

    public function commit(): bool {
        // Meilisearch data is processed via asynchronous tasks automatically.
        // Manual commits are not required.
        return true;
    }

    public function searchFoods(string $query, ?int $userId = null, int $limit = 20, int $offset = 0): array {
        try {
            $searchParams = [
                'limit'  => $limit,
                'offset' => $offset,
            ];

            // PRIVACY FILTER: Global foods (0) OR this specific user's foods
            if ($userId) {
                $searchParams['filter'] = "user_id = 0 OR user_id = {$userId}";
            } else {
                $searchParams['filter'] = "user_id = 0";
            }

            // Execute search
            $response = $this->client->index($this->indexName)->search($query, $searchParams);
            $hits = $response->getHits();

            $results = [];
            foreach ($hits as $hit) {
                $results[] = [
                    'id'          => $hit['id'] ?? null,
                    'name'        => $hit['name'] ?? 'Unknown',
                    'brand'       => $hit['brand'] ?? '',
                    'calories'    => $hit['calories'] ?? 0,
                    'ingredients' => $hit['ingredients'] ?? '',
                ];
            }

            return $results;
        } catch (ApiException $e) {
            error_log("Meilisearch search failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * @param array $foods
     * @return bool
     */
    public function indexFoodsBatch(array $foods): bool {
        try {
            $documents = [];
            foreach ($foods as $food) {
                $userId = $food->getUserId() ? (int) $food->getUserId() : 0;
                $documents[] = [
                    'id'          => $food->getId(),
                    'name'        => (string) $food->getName(),
                    'brand'       => (string) $food->getBrand(),
                    'ingredients' => (string) $food->getIngredients(),
                    'calories'    => $food->getCalories(),
                    'user_id'     => $userId
                ];
            }

            // Push the whole array of documents at once
            $this->client->index($this->indexName)->addDocuments($documents, 'id');
            return true;
        } catch (\Exception $e) {
            error_log("Meilisearch batch indexing failed: " . $e->getMessage());
            return false;
        }
    }
}