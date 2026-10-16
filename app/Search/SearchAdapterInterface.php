<?php
declare(strict_types=1);

namespace App\Search;

use App\Models\Foods;

interface SearchAdapterInterface {

    /**
     * Connect to the search engine
     * @return bool
     */
    public function connect(): bool;

    /**
     * Ensures the index is configured with the correct schema settings.
     * @return void
     */
    public function setupIndex(): void;

    /**
     * Index a single food item.
     * @param Foods $food
     * @param bool $commit
     * @return bool
     */
    public function indexFood(Foods $food, bool $commit = true): bool;

    /**
     * Batch index an array of foods for high-performance imports.
     * @param array $foods
     * @return bool
     */
    public function indexFoodsBatch(array $foods): bool;

    /**
     * Manually commit changes to the search index
     * @return bool
     */
    public function commit(): bool;

    /**
     * Search the index for foods
     * @param string $query The search term
     * @param int|null $userId Optional user ID to include personal custom foods
     * @param int $limit Max results
     * @param int $offset Pagination offset
     * @return array
     */
    public function searchFoods(string $query, ?int $userId = null, int $limit = 20, int $offset = 0): array;
}