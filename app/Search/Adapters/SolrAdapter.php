<?php
declare(strict_types=1);

namespace App\Search\Adapters;

use App\Search\SearchAdapterInterface;
use App\Models\Foods;

class SolrAdapter implements SearchAdapterInterface
{
    private \SolrClient $client;

    public function __construct(array $config) {
        // Map our generic config to the specific keys PECL SolrClient expects
        $hostConfig = $config['endpoint']['localhost'];

        $options = [
            'hostname' => $hostConfig['host'],
            'port'     => $hostConfig['port'],
            // The extension expects the path to include the core
            'path'     => $hostConfig['path'],
        ];

        $this->client = new \SolrClient($options);
    }

    public function connect(): bool {
        try {
            // The extension has a native ping method
            $pingResponse = $this->client->ping();
            return $pingResponse->success();
        } catch (\SolrClientException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function commit(): bool {
        try {
            $this->client->commit();
            return true;
        } catch (\SolrClientException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function indexFood(Foods $food, bool $commit = true): bool {
        try {
            $doc = new \SolrInputDocument();

            $doc->addField('id', strval($food->getId()));
            $doc->addField('name_t', $food->getName());
            $doc->addField('brand_t', $food->getBrand());

            // FIX 1: Added the missing _t so it matches your search query!
            $doc->addField('ingredients_t', $food->getIngredients());

            $this->client->addDocument($doc);

            if ($commit) {
                $this->client->commit();
            }

            return true;
        } catch (\SolrClientException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // Notice we added ?int $userId as a parameter here!
    public function searchFoods(string $query, ?int $userId = null, int $limit = 20, int $offset = 0): array {
        try {
            $solrQuery = new \SolrQuery();

            // 1. Sanitize the input using your existing function!
            $safeQuery = $this->escapeSolrValue($query);
            $solrQuery->setQuery($safeQuery);
            $solrQuery->setParam('defType', 'edismax');

            // 2. PRIVACY FILTER: Only return Global foods OR this specific user's foods.
            // "-user_id_i:[* TO *]" is Solr syntax for "Where user_id is NULL"
            if ($userId) {
                $solrQuery->addFilterQuery("(*:* -user_id_i:[* TO *]) OR user_id_i:{$userId}");
            } else {
                $solrQuery->addFilterQuery("*:* -user_id_i:[* TO *]");
            }

            // 3. RELEVANCE TUNING
            // Boost exact name matches aggressively (name_t^5)
            $solrQuery->setParam('qf', 'name_t^5 brand_t^2 ingredients_t');
            $solrQuery->setParam('pf', 'name_t^10 brand_t^5 ingredients_t^2');
            $solrQuery->setParam('ps', '2');

            // Tie breaker: If a word matches in multiple fields, score it slightly higher
            $solrQuery->setParam('tie', '0.1');

            // MINIMUM SHOULD MATCH (The Magic Formula)
            // "2<-25%" means:
            // - If query is 1 or 2 words, 100% of words MUST match.
            // - If query is 3+ words, 75% of words MUST match.
            $solrQuery->setParam('mm', '2<-25%');

            $solrQuery->setParam('fl', '*,score');
            $solrQuery->setStart($offset);
            $solrQuery->setRows($limit);

            $queryResponse = $this->client->query($solrQuery);
            $response = $queryResponse->getResponse();
            $results = [];

            if (isset($response['response']['docs'])) {
                foreach ($response['response']['docs'] as $doc) {
                    $results[] = [
                        'id'          => $doc['id'] ?? null,
                        'name'        => $doc['name_t'] ?? 'Unknown',
                        'brand'       => $doc['brand_t'] ?? '',
                        'calories'    => $doc['calories_i'] ?? 0,
                        'ingredients' => $doc['ingredients_t'] ?? '',
                        'score'       => $doc['score'] ?? 0,
                    ];
                }
            }

            return $results;
        } catch (\SolrClientException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function setupIndex(): void {
        // Solr's schema is typically managed externally via XML or dynamic fields.
        // No programmatic setup is required here.
    }

    public function indexFoodsBatch(array $foods): bool {
        try {
            $docs = [];
            foreach ($foods as $food) {
                $doc = new \SolrInputDocument();

                $doc->addField('id', strval($food->getId()));
                $doc->addField('name_t', $food->getName());
                $doc->addField('brand_t', $food->getBrand());
                $doc->addField('ingredients_t', $food->getIngredients());

                // CRITICAL: Map the user_id so Solr's search filter works!
                $userId = $food->getUserId() ? (int) $food->getUserId() : 0;
                $doc->addField('user_id_i', $userId);

                $docs[] = $doc;
            }

            // Push the array of documents at once
            $this->client->addDocuments($docs);
            return true;
        } catch (\SolrClientException $e) {
            error_log("Solr batch indexing failed: " . $e->getMessage());
            return false;
        }
    }

    public function escapeSolrValue($value) {
        $pattern = '/(\+|\\-|\&|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\\$1';
        return preg_replace($pattern, $replace, $value);
    }
}