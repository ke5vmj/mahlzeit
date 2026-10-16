<?php
declare(strict_types=1);

namespace App\Tasks;

use Phalcon\Cli\Task;
use App\Models\Foods;

class CustomFoodTask extends Task {

    /**
     * The default action when no action is specified.
     * Acts as a help menu for the CLI.
     */
    public function mainAction() {
        echo "Mahlzeit Custom Food CLI Tool\n";
        echo "-----------------------------\n";
        echo "Usage:\n";
        echo "  php bin/console custom_food export <user_id> <file_path.json>\n";
        echo "  php bin/console custom_food import <target_user_id> <file_path.json>\n";
        echo "\nExample:\n";
        echo "  php bin/console custom_food export 1 backups/my_foods.json\n";
    }

    /**
     * Exports a specific user's custom foods to a portable JSON file.
     */
    public function exportAction() {
        $params = $this->dispatcher->getParams();

        $userId   = (int) ($params[0] ?? 0);
        $filePath = $params[1] ?? null;

        if ($userId <= 0 || !$filePath) {
            echo "Error: Please provide a valid user ID and destination file path.\n";
            return;
        }

        $foods = Foods::find([
            'conditions' => 'user_id = :uid:',
            'bind'       => ['uid' => $userId]
        ]);

        if (count($foods) === 0) {
            echo "No custom foods found for User ID: {$userId}\n";
            return;
        }

        $exportData = [];
        foreach ($foods as $food) {
            $data = $food->toArray();

            // Strip database-specific and user-specific identifiers
            // to ensure the JSON is clean and portable.
            unset($data['id']);
            unset($data['user_id']);

            $exportData[] = $data;
        }

        // Ensure the directory exists before trying to write
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_put_contents($filePath, json_encode($exportData, JSON_PRETTY_PRINT))) {
            echo "Successfully exported " . count($exportData) . " foods to {$filePath}\n";
        } else {
            echo "Error: Could not write to file -> {$filePath}\n";
        }
    }

    /**
     * Imports custom foods from a JSON file and assigns them to a specific user.
     */
    public function importAction() {
        $params = $this->dispatcher->getParams();

        $userId   = (int) ($params[0] ?? 0);
        $filePath = $params[1] ?? null;

        if ($userId <= 0 || !$filePath || !file_exists($filePath)) {
            echo "Error: Please provide a valid target user ID and a valid file path.\n";
            return;
        }

        $json = file_get_contents($filePath);
        $importData = json_decode($json, true);

        if (!$importData || !is_array($importData)) {
            echo "Error: Invalid JSON file format.\n";
            return;
        }

        // Grab the search adapter from the DI container
        $search = $this->getDI()->getShared('search');

        $successCount = 0;
        $errorCount   = 0;

        echo "Starting import for User ID: {$userId}...\n";

        foreach ($importData as $item) {
            $food = new Foods();

            // Mass assign the attributes from the JSON
            $food->assign($item);

            // Explicitly map this food to the new target user
            $food->setUserId($userId);

            // Safety fallback just in case the JSON lacked a barcode
            if (!$food->getBarcode()) {
                $food->setBarcode('user_item');
            }

            if ($food->save()) {
                $successCount++;

                // Index it into Solr/Redis instantly.
                // We pass `false` here so the search engine queues it in RAM
                // instead of thrashing the disk with a hard commit on every row.
                $search->indexFood($food, false);
            } else {
                $errorCount++;
                // Uncomment below if you want to debug specific failing rows
                $messages = implode(", ", $food->getMessages());
                echo "Failed to import '{$item['name']}': {$messages}\n";
            }
        }

        // Execute one final hard commit to the search engine to save everything to disk
        $search->commit();

        echo "Import Complete! Successfully imported: {$successCount}. Errors: {$errorCount}.\n";
    }
}