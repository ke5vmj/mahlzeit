<?php
declare(strict_types=1);

namespace App\Tasks;

use Phalcon\Cli\Task;
use App\Models\Foods;
use JsonMachine\Items;
use JsonMachine\Exception\PathNotFoundException;

class ImportTask extends Task
{
    public function mainAction()
    {
        echo "Kilo Data Importer" . PHP_EOL;
        echo "------------------" . PHP_EOL;
        echo "Usage: php bin/console import foods <file_path> <provider>" . PHP_EOL;
        echo "Providers: fooddata_api, open_foods_api" . PHP_EOL;
    }

    /**
     * Action to import foods from a gzipped JSON file.
     * CLI args map to the $params array automatically.
     */
    public function foodsAction() {
        $params = $this->dispatcher->getParams();

        $filePath = $params[0] ?? null;
        $provider = $params[1] ?? 'fooddata_api';

        if (!$filePath || !file_exists($filePath)) {
            echo "Error: Please provide a valid file path." . PHP_EOL;
            return;
        }

        echo "Starting food import from {$filePath} using {$provider}..." . PHP_EOL;

        $streamHandle = null;
        try {
            // 1. Prepare to read the gzipped file
            $uncompressedStreamUri = 'compress.zlib://' . realpath($filePath);
            $streamHandle = fopen($uncompressedStreamUri, 'r');

            if ($streamHandle === false) {
                throw new \Exception("Could not open a stream to the gzipped file.");
            }

            // 2. Setup the Iterator based on the provider/format
            if ($provider === 'open_foods_api') {
                // JSON Lines (.jsonl) - Native PHP Generator
                $foodItems = (function() use ($streamHandle) {
                    while (($line = fgets($streamHandle)) !== false) {
                        $line = trim($line);
                        if ($line !== '') {
                            yield json_decode($line); // Yields one object at a time
                        }
                    }
                })();
            } else {
                // Standard JSON (.json) - JsonMachine Streaming
                $foodItems = Items::fromStream($streamHandle, ['pointer' => '/BrandedFoods']);
            }

            $itemCount = 0;
            $errorCount = 0;

            // Grab the Search Service from the DI
            $search = $this->getDI()->getShared('search');

            // 3. Loop and save
            foreach ($foodItems as $foodItem) {
                $language = $foodItem->lc ?? 'en';
                if ($language !== 'en') {
                    continue;
                }

                $parsed = ($provider === 'open_foods_api')
                    ? $this->parseOpenFoods($foodItem)
                    : $this->parseFoodService($foodItem);

                if(empty($parsed['nutrients']['calories']) || (int) $parsed['nutrients']['calories'] > 2000) {
                    continue;
                }

                // Use the generated Phalcon Model
                $food = new Foods();

                $food->assign([
                    'user_id'        => null,
                    'name'           => $this->truncate($parsed['description'], 200),
                    'brand'          => $this->truncate($parsed['brandedFoodCategory'], 100),
                    'barcode'        => $parsed['barcode'] ?? 0,

                    // Cap the values at 9999.99 so they fit in DECIMAL(6,2)
                    'serving_size'   => min(9999.99, (float) $parsed['servingSizeAmount']),
                    'serving_unit'   => $this->truncate($parsed['servingSizeUnit'], 50),

                    'calories'       => (int) $parsed['nutrients']['calories'],
                    'protein_g'      => min(9999.99, (float) $parsed['nutrients']['protein']),
                    'fat_g'          => min(9999.99, (float) $parsed['nutrients']['fat']),
                    'carbs_g'        => min(9999.99, (float) $parsed['nutrients']['carbohydrates']),

                    'fiber_g'        => min(9999.99, (float) $parsed['nutrients']['fiber']),
                    'sugar_g'        => min(9999.99, (float) $parsed['nutrients']['sugars']),

                    // These are integers, so they can naturally hold up to ~2 billion, no capping needed
                    'sodium_mg'      => (int) $parsed['nutrients']['sodium'],
                    'cholesterol_mg' => (int) $parsed['nutrients']['cholesterol'],
                    'calcium_mg'     => (int) $parsed['nutrients']['calcium'],
                    'iron_mg'        => (int) $parsed['nutrients']['iron'],
                    'ingredients'    => $parsed['ingredients'] ?? null,
                ]);

                // Save to database
                if ($food->save() === false) {
                    $errorCount++;
                    // Optional: print validation errors
                    // foreach ($food->getMessages() as $message) { echo $message . PHP_EOL; }
                } else {
                    $itemCount++;

                    // Push to Solr immediately!
                    // Pass 'false' so Solr keeps it in memory and doesn't write to disk yet
                    $search->indexFood($food, false);
                }

                // Simple CLI progress indicator
                if ($itemCount % 1000 === 0) {
                    echo "Processed {$itemCount} records..." . PHP_EOL;

                    // Optional: Commit to Solr every 1000 records to keep memory usage low
                    $search->commit();
                }
            }

            // The loop is done. Send one final commit to Solr to save the rest!
            $search->commit();

            echo "Import Complete! Successfully added: {$itemCount}. Errors: {$errorCount}." . PHP_EOL;

        } catch (PathNotFoundException $e) {
            echo "Error: The expected data structure was not found in the JSON file." . PHP_EOL;
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . PHP_EOL;
        } finally {
            if (is_resource($streamHandle)) {
                fclose($streamHandle);
            }
        }
    }

    /**
     * Reindexes all items from the database into the Search provider
     *
     * @return void
     */
    public function reindexAction() {
        /** @var \App\Search\SearchAdapterInterface $search */
        $search = $this->getDI()->getShared('search');

        // We no longer need method_exists().
        // The interface guarantees this will run safely on any adapter.
        $search->setupIndex();

        // Fetch records in chunks to save RAM and speed up network requests
        $chunkSize = 5000;
        $offset = 0;

        while (true) {
            $foods = \App\Models\Foods::find([
                'limit'  => $chunkSize,
                'offset' => $offset
            ]);

            if (count($foods) === 0) {
                break; // We are done!
            }

            // Convert Phalcon ResultSet to standard array for the adapter
            $foodsArray = [];
            foreach ($foods as $food) {
                $foodsArray[] = $food;
            }

            $search->indexFoodsBatch($foodsArray);

            $offset += $chunkSize;
        }

        echo "Reindexing complete!";
    }

    /**
     * Parses FoodData API Object
     */
    private function parseFoodService(object $foodItem): array {
        $nutrients = $foodItem->labelNutrients ?? null;
        return [
            'description'         => $foodItem->description ?? 'Unknown Food',
            'servingSizeAmount'   => $foodItem->servingSize ?? 0,
            'servingSizeUnit'     => $foodItem->servingSizeUnit ?? 'g',
            'brandedFoodCategory' => trim($foodItem->brandedFoodCategory ?? ''),
            'nutrients' => [
                'calories'      => $nutrients->calories->value ?? 0,
                'fat'           => $nutrients->fat->value ?? 0,
                'protein'       => $nutrients->protein->value ?? 0,
                'carbohydrates' => $nutrients->carbohydrates->value ?? 0,
                'fiber'         => $nutrients->fiber->value ?? 0,
                'sugars'        => $nutrients->sugars->value ?? 0,
                'sodium'        => $nutrients->sodium->value ?? 0,
                'cholesterol'   => $nutrients->cholesterol->value ?? 0,
                'calcium'       => $nutrients->calcium->value ?? 0,
                'iron'          => $nutrients->iron->value ?? 0,
            ],
            'ingredients' => $foodItem->ingredients,
        ];
    }

    /**
     * Parses Open Foods API Object
     */
    private function parseOpenFoods(object $foodItem): array {
        $nutrients = $foodItem->nutriments ?? null;

        // 1. Establish the serving quantity safely
        $servingQuantity = 100.0;
        if (isset($foodItem->serving_quantity) && is_numeric($foodItem->serving_quantity) && $foodItem->serving_quantity > 0) {
            $servingQuantity = (float) $foodItem->serving_quantity;
        }

        // 2. Helper function for per-serving metrics
        $getPerServing = function($nutrientBaseName) use ($nutrients, $servingQuantity) {
            if (!$nutrients) return 0.0;

            $servingKey = $nutrientBaseName . '_serving';
            if (isset($nutrients->{$servingKey}) && is_numeric($nutrients->{$servingKey})) {
                return (float) $nutrients->{$servingKey};
            }

            $per100gKey = $nutrientBaseName . '_100g';
            if (isset($nutrients->{$per100gKey}) && is_numeric($nutrients->{$per100gKey})) {
                $per100gValue = (float) $nutrients->{$per100gKey};
                return round(($per100gValue / 100) * $servingQuantity, 2);
            }

            return 0.0;
        };

        // 3. The Calorie Waterfall
        $calories = null;
        if ($nutrients) {
            if (isset($nutrients->{'energy-kcal_serving'}) && is_numeric($nutrients->{'energy-kcal_serving'})) {
                $calories = (float) $nutrients->{'energy-kcal_serving'};
            } elseif (isset($nutrients->{'energy-kcal_100g'}) && is_numeric($nutrients->{'energy-kcal_100g'})) {
                $calories = round(((float) $nutrients->{'energy-kcal_100g'} / 100) * $servingQuantity, 2);
            } elseif (isset($nutrients->{'energy_serving'}) && is_numeric($nutrients->{'energy_serving'})) {
                $calories = $this->kjToKcal((float) $nutrients->{'energy_serving'});
            } elseif (isset($nutrients->{'energy_100g'}) && is_numeric($nutrients->{'energy_100g'})) {
                $kjPerServing = (($nutrients->{'energy_100g'} / 100) * $servingQuantity);
                $calories = $this->kjToKcal($kjPerServing);
            }
        }

        if ($calories === null) {
            $calories = 0.0;
        }

        // Fetch macros needed for the sanity check
        $fat = $getPerServing('fat');
        $protein = $getPerServing('proteins');
        $carbs = $getPerServing('carbohydrates');

        // --- THE SANITY CHECKER ---
        // Calculate what the calories SHOULD be based on the macros
        $calculatedCalories = ($protein * 4) + ($carbs * 4) + ($fat * 9);

        // If the stated calories are absurdly high (> 10,000) OR
        // if they mismatch the calculated macros by more than 20%, trust the math instead.
        if ($calories > 10000 || ($calculatedCalories > 0 && abs($calories - $calculatedCalories) > (max($calories, 1) * 0.20))) {
            //error_log("Calorie anomaly fixed for: " . ($foodItem->product_name ?? 'unknown') . ". Original: $calories, Calculated: $calculatedCalories.");
            $calories = $calculatedCalories;
        }

        // Final safety net: ensure it's cast to an integer for your MySQL column
        $finalCalories = (int) round($calories);

        // 4. Return the fully normalized array
        return [
            'description'         => $foodItem->product_name ?? 'Unknown Food',
            'barcode'             => $foodItem->code,
            'servingSizeAmount'   => $servingQuantity,
            'servingSizeUnit'     => $foodItem->product_quantity_unit ?? 'g',
            'brandedFoodCategory' => trim($foodItem->brands ?? ''),
            'nutrients' => [
                'calories'      => $finalCalories,
                'fat'           => $fat,
                'protein'       => $protein,
                'carbohydrates' => $carbs,
                'fiber'         => $getPerServing('fiber'),
                'sugars'        => $getPerServing('sugars'),
                'sodium'        => $getPerServing('sodium'),
                'cholesterol'   => $getPerServing('cholesterol'),
                'calcium'       => $getPerServing('calcium'),
                'iron'          => $getPerServing('iron'),
                'potassium'     => $getPerServing('potassium'),
            ],
            'ingredients' => $foodItem->ingredients_text ?? '',
        ];
    }

    private function truncate(string $string, int $limit): string {
        if (mb_strlen($string) <= $limit) {
            return $string;
        }
        return mb_substr($string, 0, $limit - 3) . '...';
    }

    // Helper to convert kJ to kcal
    function kjToKcal($kj) {
        return round($kj / 4.184, 2);
    }
}