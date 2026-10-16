<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Foods;

class FoodController extends ControllerBase {

    /**
     * Orchestrates the food search and discovery interface.
     *
     * This method acts as a central hub for food selection, supporting four distinct modes:
     * 1. Active Search: Integration with Solr for high-performance full-text search.
     * 2. Recents: Retrieves foods previously logged by the user, sorted by recency.
     * 3. Pinned: Fetches user-bookmarked or "favorite" food items.
     * 4. My Foods: Lists food entries specifically created by the authenticated user.
     *
     * The method handles manual pagination by fetching (N+1) items to determine
     * next-page availability and utilizes SQL `FIELD()` ordering to maintain
     * relevance from Solr or chronological order from history logs.
     *
     * @param string $date       The target date for the eventual diary entry (Y-m-d).
     * @param string $mealTypeId The ID of the meal category (e.g., Breakfast, Snack).
     *
     * @return void Sets 'foods', 'activeTab', 'hasNextPage', and 'mealName' to $this->view.
     */
    public function searchAction(string $date, string $mealTypeId) {
        // 1. Initial View Setup
        $this->view->date = $date;
        $this->view->mealTypeId = (int) $mealTypeId;

        $mealType = \App\Models\MealTypes::findFirst((int)$mealTypeId);
        $this->view->mealName = $mealType ? $mealType->getName() : "Meal";
        $this->session->set('LAST_FOOD_SEARCH_URL', $this->request->getURI());

        // 2. Input & Pagination Setup
        $userId = $this->getCurrentUserId();
        $rawQuery = $this->request->getQuery('q');
        $query    = $rawQuery !== null ? trim((string) $rawQuery) : '';

        // Automatically force the 'search' tab if a query is present
        $tab  = $query !== '' ? 'search' : $this->request->getQuery('tab', 'string', 'recent');
        $page = max(1, $this->request->getQuery('page', 'int', 1));

        $limit = 20;
        $offset = ($page - 1) * $limit;
        $fetchLimit = $limit + 1; // Fetch 1 extra to determine "Next" page

        $foods = [];

        // 3. Data Fetching Routing
        switch ($tab) {
            case 'search':
                $ids = $this->getSearchIds($query, $userId, $fetchLimit, $offset);
                $foods = $this->getFoodsByIds($ids);
                break;
            case 'recent':
                $ids = $this->getRecentIds($userId, $fetchLimit, $offset);
                $foods = $this->getFoodsByIds($ids);
                break;
            case 'pinned':
                $ids = $this->getPinnedIds($userId, $fetchLimit, $offset);
                $foods = $this->getFoodsByIds($ids);
                break;
            case 'my_foods':
                $foods = $this->getMyFoods($userId, $fetchLimit, $offset);
                break;
            case 'templates':
                $this->view->templates = $this->getTemplates($userId);
                break;
        }

        // 4. Unified Pagination Check
        $hasNextPage = false;
        if (count($foods) > $limit) {
            $hasNextPage = true;
            array_pop($foods); // Remove the 21st item
        }

        // 5. Final View Assignment
        $this->view->searchQuery = $query;
        $this->view->activeTab   = $tab;
        $this->view->foods       = $foods;
        $this->view->currentPage = $page;
        $this->view->hasNextPage = $hasNextPage;
    }

    /**
     * Searches for foods via Solr and returns an array of their IDs.
     *
     * @param string $query  The active search term provided by the user.
     * @param int    $userId The ID of the current user.
     * @param int    $limit  The maximum number of results to fetch (includes +1 for pagination).
     * @param int    $offset The pagination offset based on the current page.
     *
     * @return int[] An array of integer food IDs.
     */
    private function getSearchIds(string $query, int $userId, int $limit, int $offset): array {
        $solrResults = $this->search->searchFoods($query, $userId, $limit, $offset);
        return array_column($solrResults, 'id');
    }

    /**
     * Retrieves the IDs of foods recently eaten/logged by the user.
     *
     * @param int $userId The ID of the current user.
     * @param int $limit  The maximum number of results to fetch.
     * @param int $offset The pagination offset.
     *
     * @return int[] An array of recently logged food IDs.
     */
    private function getRecentIds(int $userId, int $limit, int $offset): array {
        $recentLogs = \App\Models\UserFavorites::find([
            'conditions' => 'user_id = :uid: AND last_eaten_at IS NOT NULL',
            'bind'       => ['uid' => $userId],
            'order'      => 'last_eaten_at DESC',
            'limit'      => $limit,
            'offset'     => $offset
        ]);
        return array_column(iterator_to_array($recentLogs), 'food_id');
    }

    /**
     * Retrieves the IDs of foods specifically pinned/favorited by the user.
     *
     * @param int $userId The ID of the current user.
     * @param int $limit  The maximum number of results to fetch.
     * @param int $offset The pagination offset.
     *
     * @return int[] An array of pinned food IDs.
     */
    private function getPinnedIds(int $userId, int $limit, int $offset): array {
        $favorites = \App\Models\UserFavorites::find([
            'conditions' => 'user_id = :uid: AND is_pinned = 1',
            'bind'       => ['uid' => $userId],
            'limit'      => $limit,
            'offset'     => $offset
        ]);
        return array_column(iterator_to_array($favorites), 'food_id');
    }

    /**
     * Fetches actual Food model instances based on an array of IDs.
     * Ensures the returned models maintain the exact sorting order of the provided IDs.
     *
     * @param int[] $ids An array of food IDs to hydrate.
     *
     * @return \App\Models\Foods[] An array of Food model instances.
     */
    private function getFoodsByIds(array $ids): array {
        if (empty($ids)) return [];

        $idsStr = implode(',', $ids);
        $foodsResultSet = \App\Models\Foods::find([
            'conditions' => 'id IN ({ids:array})',
            'bind'       => ['ids' => $ids],
            'order'      => "FIELD(id, $idsStr)" // Preserve Solr/Recent relevance order
        ]);

        return iterator_to_array($foodsResultSet);
    }

    /**
     * Fetches custom foods directly created by the user.
     * * Note: Ensure `user_id` is indexed in the database to prevent full table scans.
     *
     * @param int $userId The ID of the current user.
     * @param int $limit  The maximum number of results to fetch.
     * @param int $offset The pagination offset.
     *
     * @return \App\Models\Foods[] An array of custom Food model instances.
     */
    private function getMyFoods(int $userId, int $limit, int $offset): array {
        $myFoods = \App\Models\Foods::find([
            'conditions' => 'user_id = :uid:',
            'bind'       => ['uid' => $userId],
            'order'      => 'name ASC',
            'limit'      => $limit,
            'offset'     => $offset
        ]);
        return iterator_to_array($myFoods);
    }

    /**
     * Retrieves all meal templates belonging to the user.
     *
     * @param int $userId The ID of the current user.
     *
     * @return \Phalcon\Mvc\Model\ResultsetInterface|\App\Models\MealTemplates[] A resultset of MealTemplates.
     */
    private function getTemplates(int $userId) {
        return \App\Models\MealTemplates::find([
            'conditions' => 'user_id = :uid:',
            'bind'       => ['uid' => $userId],
            'order'      => 'name ASC'
        ]);
    }

    /**
     * Displays the nutritional details for a specific food item.
     *
     * This method prepares the view for the user to review food data and
     * specify quantities before logging. It checks the user's favorites
     * to determine the "pinned" status of the item and manages the "Back"
     * navigation state using session data.
     *
     * Key responsibilities:
     * - Validates existence of the food item.
     * - Checks for user-specific 'pinned' (favorite) status.
     * - Resolves the return URL to ensure the user returns to their
     *   previous search results or tab.
     *
     * @param string $date       The target date for the diary entry (Y-m-d).
     * @param string $id         The unique ID of the food item being viewed.
     * @param string $mealTypeId The ID of the meal category (e.g., Lunch, Dinner).
     *
     * @return \Phalcon\Http\Response|void Redirects to search if food is missing,
     *                                     otherwise populates the view.
     */
    public function detailsAction(string $date, string $id, string $mealTypeId) {
        $food = \App\Models\Foods::findFirst((int) $id);

        if (!$food) {
            return $this->response->redirect("/diary/{$date}/add-food/{$mealTypeId}");
        }

        // Check if the user has favorited this food
        $favorite = \App\Models\UserFavorites::findFirst([
            'conditions' => 'user_id = :uid: AND food_id = :fid:',
            'bind'       => [
                'uid' => $this->getCurrentUserId(),
                'fid' => $food->getId()
            ]
        ]);

        $this->view->isPinned = ($favorite && $favorite->is_pinned == 1);
        $this->view->food = $food;

        // ---- Retrieve the exact URL they came from ----
        // If they jumped straight here via a bookmark, default back to the recent tab safely.
        $this->view->backUrl = $this->session->get('LAST_FOOD_SEARCH_URL', "/diary/{$date}/add-food/{$mealTypeId}");

        $this->view->date = $date;
        $this->view->mealTypeId = (int) $mealTypeId;
    }

    /**
     * Prepares the view to edit or refine a food entry selection.
     *
     * Loads the specific food model to display its nutritional information and
     * allows the user to adjust parameters (like quantity or serving size)
     * before finalizing the log. If the food item cannot be found, the user
     * is redirected back to the search interface for that specific meal.
     *
     * @param string $date       The diary date being edited (Y-m-d).
     * @param string $id         The unique ID of the food item.
     * @param string $mealTypeId The ID of the meal category (e.g., Breakfast, Snacks).
     *
     * @return \Phalcon\Http\Response|void Redirects to the search page on failure,
     *                                     otherwise populates the view.
     */
    public function editAction(string $date, string $id, string $mealTypeId) {
        $food = \App\Models\Foods::findFirst((int) $id);

        if (!$food) {
            return $this->response->redirect("/diary/{$date}/add-food/{$mealTypeId}");
        }

        $this->view->food = $food;
        $this->view->date = $date;
        $this->view->mealTypeId = (int) $mealTypeId;
    }

    /**
     * Updates the nutritional profile of a specific food item.
     *
     * Processes a POST request containing comprehensive food data, including
     * basic identity (name, brand), macronutrients, and micronutrients.
     *
     * Key Logic:
     * 1. Enforces POST-only access.
     * 2. Mass-assigns sanitized inputs to the Food model.
     * 3. Upon successful persistence to MySQL, triggers an immediate
     *    Solr index update with a hard commit to ensure the search
     *    engine reflects the new data instantly.
     *
     * @param string $date       The diary date context for redirection (Y-m-d).
     * @param string $id         The unique identifier of the food record to update.
     * @param string $mealTypeId The meal category context for redirection.
     *
     * @return \Phalcon\Http\Response Redirects back to the food details view.
     */
    public function updateAction(string $date, string $id, string $mealTypeId) {
        if (!$this->request->isPost()) {
            return $this->response->redirect("/diary/{$date}/food/{$id}/details/{$mealTypeId}");
        }

        $food = \App\Models\Foods::findFirst((int) $id);

        if ($food) {
            // Assign the updated values from the form
            $food->assign([
                'name'           => $this->request->getPost('name', 'string'),
                'brand'          => $this->request->getPost('brand', 'string'),
                'serving_size'   => $this->request->getPost('serving_size', 'float'),
                'serving_unit'   => $this->request->getPost('serving_unit', 'string'),

                // Macros
                'calories'       => $this->request->getPost('calories', 'int'),
                'protein_g'      => $this->request->getPost('protein_g', 'float'),
                'carbs_g'        => $this->request->getPost('carbs_g', 'float'),
                'fat_g'          => $this->request->getPost('fat_g', 'float'),

                // Micronutrients
                'fiber_g'        => $this->request->getPost('fiber_g', 'float', 0),
                'sugar_g'        => $this->request->getPost('sugar_g', 'float', 0),
                'cholesterol_mg' => $this->request->getPost('cholesterol_mg', 'int', 0),
                'sodium_mg'      => $this->request->getPost('sodium_mg', 'int', 0),
                'calcium_mg'     => $this->request->getPost('calcium_mg', 'int', 0),
                'iron_mg'        => $this->request->getPost('iron_mg', 'int', 0),

                // Ingredients (Text area)
                'ingredients'    => $this->request->getPost('ingredients', 'string', null),
            ]);
            // Save to MySQL
            if ($food->save()) {
                // Instantly sync the changes to Apache Solr!
                // Passing 'true' commits it to the disk immediately.
                $this->search->indexFood($food, true);
            }
        }

        // Redirect back to the Food Details screen
        return $this->response->redirect("/diary/{$date}/food/{$id}/details/{$mealTypeId}");
    }

    /**
     * Toggles the "pinned" (favorite) status of a food item for the current user.
     *
     * This method acts as an upsert handler:
     * - If a favorite record exists, it flips the 'is_pinned' boolean.
     * - If no record exists, it creates a new UserFavorites entry and sets it to pinned.
     *
     * The method requires 'date' and 'meal_type_id' via POST to maintain the
     * user's navigation context upon redirection.
     *
     * @param string $id The unique identifier of the food item to toggle.
     *
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     *         Redirects back to the food details page.
     */
    public function togglePinAction(string $id) {
        if (!$this->request->isPost()) {
            return $this->response->redirect('/diary');
        }

        $userId = $this->getCurrentUserId();
        $foodId = (int) $id;

        // We need the date and mealType to redirect them right back to where they were
        $date = $this->request->getPost('date', 'string');
        $mealTypeId = $this->request->getPost('meal_type_id', 'int');

        $favorite = \App\Models\UserFavorites::findFirst([
            'conditions' => 'user_id = :uid: AND food_id = :fid:',
            'bind'       => ['uid' => $userId, 'fid' => $foodId]
        ]);

        if ($favorite) {
            // Toggle it! (If 1, make 0. If 0, make 1)
            $favorite->is_pinned = $favorite->is_pinned ? 0 : 1;
        } else {
            // Doesn't exist yet, create it and pin it!
            $favorite = new \App\Models\UserFavorites();
            $favorite->user_id = $userId;
            $favorite->food_id = $foodId;
            $favorite->is_pinned = 1;
            // Provide a default fallback for last_eaten_at just in case your DB requires it
            $favorite->last_eaten_at = date('Y-m-d H:i:s');
        }

        $favorite->save();

        return $this->response->redirect("/diary/{$date}/food/{$foodId}/details/{$mealTypeId}");
    }

    public function createAction(string $date, string $mealTypeId) {
        $this->view->date = $date;
        $this->view->mealTypeId = (int) $mealTypeId;
    }

    /**
     * Creates and persists a new custom food item.
     *
     * Handles the POST submission for user-defined food products. This method:
     * 1. Associates the new food item with the authenticated user's ID.
     * 2. Sanitizes and assigns macronutrient and micronutrient data.
     * 3. Persists the record to the database.
     * 4. Synchronizes the new record with the Solr search index for immediate availability.
     * 5. Redirects to the details view of the newly created item upon success.
     *
     * @param string $date       The current diary date context (Y-m-d).
     * @param string $mealTypeId The ID of the meal category context.
     *
     * @return \Phalcon\Http\Response Redirects to the details page on success,
     *                                     or back to the creation form on failure.
     */
    public function storeAction(string $date, string $mealTypeId) {
        if (!$this->request->isPost()) {
            return $this->response->redirect("/diary/{$date}/add-food/{$mealTypeId}");
        }

        $food = new \App\Models\Foods();

        // Assign all the form values, making sure to attach the Current User ID!
        $food->assign([
            'user_id'        => $this->getCurrentUserId(),
            'barcode'        => 'user_item',
            'name'           => $this->request->getPost('name', 'string'),
            'brand'          => $this->request->getPost('brand', 'string'),
            'serving_size'   => $this->request->getPost('serving_size', 'float'),
            'serving_unit'   => $this->request->getPost('serving_unit', 'string'),

            // Macros
            'calories'       => $this->request->getPost('calories', 'int'),
            'protein_g'      => $this->request->getPost('protein_g', 'float'),
            'carbs_g'        => $this->request->getPost('carbs_g', 'float'),
            'fat_g'          => $this->request->getPost('fat_g', 'float'),

            // Micronutrients
            'fiber_g'        => $this->request->getPost('fiber_g', 'float', 0),
            'sugar_g'        => $this->request->getPost('sugar_g', 'float', 0),
            'cholesterol_mg' => $this->request->getPost('cholesterol_mg', 'int', 0),
            'sodium_mg'      => $this->request->getPost('sodium_mg', 'int', 0),
            'calcium_mg'     => $this->request->getPost('calcium_mg', 'int', 0),
            'iron_mg'        => $this->request->getPost('iron_mg', 'int', 0),

            // Ingredients
            'ingredients'    => $this->request->getPost('ingredients', 'string', ''),
        ]);

        if ($food->save()) {
            // Instantly sync the new food to Apache Solr so it's searchable!
            $this->search->indexFood($food, true);

            $this->flashSession->success("Custom food created successfully!");

            // Redirect them straight to the details page of their newly created food
            return $this->response->redirect("/diary/{$date}/food/{$food->getId()}/details/{$mealTypeId}");
        }

        $this->flashSession->error("Failed to create food. Please check your inputs.");
        return $this->response->redirect("/diary/{$date}/food/create/{$mealTypeId}");
    }

    /**
     * Renders the barcode scanner interface.
     *
     * This method serves as the entry point for the camera-based scanning UI.
     * It passes the necessary context (date and meal category) to the view
     * so that once a barcode is successfully captured via JavaScript, the
     * browser can proceed to the lookup endpoint with the correct parameters.
     *
     * @param string $date       The target diary date for the eventual log (Y-m-d).
     * @param string $mealTypeId The ID of the meal category (e.g., Breakfast, Lunch).
     *
     * @return void Sets 'date' and 'mealTypeId' to $this->view.
     */
    public function scanAction(string $date, string $mealTypeId) {
        $this->view->date = $date;
        $this->view->mealTypeId = (int) $mealTypeId;
    }

    /**
     * Look up a food item by its UPC/EAN barcode.
     *
     * This action acts as a router for the barcode scanning feature. It attempts
     * to find an exact string match for a barcode in the database.
     *
     * Flow:
     * - If found: Redirects the user directly to the food details page for logging.
     * - If not found: Redirects to the food creation form, passing the barcode
     *   along so the user can register the new item without re-typing the code.
     *
     * @param string $date       The target diary date (Y-m-d).
     * @param string $mealTypeId The ID of the meal category context.
     *
     * @return \Phalcon\Http\Response Redirects to details on success, create
     *                                 form on miss, or scan page on empty input.
     */
    public function lookupBarcodeAction(string $date, string $mealTypeId) {
        $barcode = $this->request->getQuery('code', 'string');

        if (!$barcode) {
            $this->flashSession->error("No barcode detected.");
            return $this->response->redirect("/diary/{$date}/food/scan/{$mealTypeId}");
        }

        // Exact match lookup in MySQL
        $food = \App\Models\Foods::findFirst([
            'conditions' => 'barcode = :code:',
            'bind'       => ['code' => $barcode]
        ]);

        if ($food) {
            // Food found! Send them to log it.
            $this->flashSession->success("Found: " . $food->getName());
            return $this->response->redirect("/diary/{$date}/food/{$food->getId()}/details/{$mealTypeId}");
        } else {
            // Food not found! Send them to create it, passing the barcode via query string
            $this->flashSession->notice("New barcode! Let's add this food to the database.");
            return $this->response->redirect("/diary/{$date}/food/create/{$mealTypeId}?barcode={$barcode}");
        }
    }
}