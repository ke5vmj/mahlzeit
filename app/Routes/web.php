<?php
/**
 * Define your routes here.
 * The $router variable is available from the scope in RouterProvider.
 */

// Registration
$router->addGet('/register', [
    'controller' => 'auth',
    'action'     => 'register'
]);
$router->addPost('/register', [
    'controller' => 'auth',
    'action'     => 'doRegister'
]);

// Login
$router->addGet('/login', [
    'controller' => 'auth',
    'action'     => 'login'
]);
$router->addPost('/login', [
    'controller' => 'auth',
    'action'     => 'doLogin'
]);

// Logout
$router->addGet('/logout', [
    'controller' => 'auth',
    'action'     => 'logout'
]);

// Example: Home Route
$router->addGet('/', [
    'controller' => 'index',
    'action'     => 'index',
]);

// 404 Handler (Optional)
$router->notFound([
    'controller' => 'index',
    'action'     => 'notFound',
]);

// The Add Food Search Screen
$router->addGet('/diary/{date}/add-food/{meal_type_id}', [
    'controller' => 'food',
    'action'     => 'search'
]);

// The Add Exercise Search Screen
$router->addGet('/diary/{date}/add-exercise', [
    'controller' => 'exercise',
    'action'     => 'search'
]);

// The endpoint that actually saves the exercise log
$router->addPost('/diary/log-exercise', [
    'controller' => 'exercise',
    'action'     => 'log'
]);

// The Food Details / Adjustment Screen
$router->addGet('/diary/{date}/food/{id}/details/{meal_type_id}', [
    'controller' => 'food',
    'action'     => 'details'
]);

// Show the Edit Form
$router->addGet('/diary/{date}/food/{id}/edit/{meal_type_id}', [
    'controller' => 'food',
    'action'     => 'edit'
]);

// Process the Edit Form
$router->addPost('/diary/{date}/food/{id}/edit/{meal_type_id}', [
    'controller' => 'food',
    'action'     => 'update'
]);

// The Main Diary Dashboard (defaults to today)
$router->addGet('/diary', [
    'controller' => 'diary',
    'action'     => 'index'
]);

// The Diary Dashboard for a specific date
$router->addGet('/diary/{date}', [
    'controller' => 'diary',
    'action'     => 'index'
]);

// Process the Add to Diary form submission
$router->addPost('/diary/log', [
    'controller' => 'diary',
    'action'     => 'log'
]);

// Delete a Diary Entry
$router->addPost('/diary/entry/{id}/delete', [
    'controller' => 'diary',
    'action'     => 'delete'
]);

// --- NEW: Edit a Diary Entry ---
$router->addGet('/diary/entry/{id}/edit', [
    'controller' => 'diary',
    'action'     => 'edit'
]);

// --- NEW: Process the Diary Entry Update ---
$router->addPost('/diary/entry/{id}/update', [
    'controller' => 'diary',
    'action'     => 'update'
]);

// Process the Add Exercise Form
$router->addPost('/diary/exercise/log', [
    'controller' => 'exercise',
    'action'     => 'log'
]);

// Delete an Exercise Entry
$router->addPost('/diary/exercise/{id}/delete', [
    'controller' => 'exercise',
    'action'     => 'delete'
]);

// Exercise Search
$router->addGet('/diary/{date}/exercise/search', [
    'controller' => 'exercise',
    'action'     => 'search'
]);

// Exercise Add (Now takes the chosen Activity ID)
$router->addGet('/diary/{date}/exercise/add/{activityId}', [
    'controller' => 'exercise',
    'action'     => 'add'
]);

// Show the Log Weight Form
$router->addGet('/diary/{date}/log-weight', [
    'controller' => 'weight',
    'action'     => 'add'
]);

// Process the Log Weight Form
$router->addPost('/diary/weight/log', [
    'controller' => 'weight',
    'action'     => 'log'
]);

// Show the Onboarding Wizard
$router->addGet('/onboarding', [
    'controller' => 'onboarding',
    'action'     => 'index'
]);

// Process the Onboarding Data
$router->addPost('/onboarding/save', [
    'controller' => 'onboarding',
    'action'     => 'save'
]);

// Settings Dashboard
$router->addGet('/settings', [
    'controller' => 'settings',
    'action'     => 'index'
]);

// Add a Custom Meal Type
$router->addPost('/settings/meal/add', [
    'controller' => 'settings',
    'action'     => 'addMeal'
]);

// Update Profile Settings
$router->addPost('/settings/profile/update', [
    'controller' => 'settings',
    'action'     => 'updateProfile'
]);

// Delete a Meal Type
$router->addPost('/settings/meal/{id}/delete', [
    'controller' => 'settings',
    'action'     => 'deleteMeal'
]);

// Goals Dashboard
$router->addGet('/goals', [
    'controller' => 'goals',
    'action'     => 'index'
]);

// Goals Save action
$router->addPost('/goals/save', [
    'controller' => 'goals',
    'action'     => 'save'
]);

// Toggle Pin Status for a Food
$router->addPost('/diary/food/{id}/pin', [
    'controller' => 'food',
    'action'     => 'togglePin'
]);

// Show the Create Food Form
$router->addGet('/diary/{date}/food/create/{mealTypeId}', [
    'controller' => 'food',
    'action'     => 'create'
]);

// Process the Create Food Form
$router->addPost('/diary/{date}/food/create/{mealTypeId}', [
    'controller' => 'food',
    'action'     => 'store'
]);

// Trends Dashboard
$router->addGet('/trends', [
    'controller' => 'trends',
    'action'     => 'index'
]);

// Show the Barcode Scanner Camera
$router->addGet('/diary/{date}/food/scan/{mealTypeId}', [
    'controller' => 'food',
    'action'     => 'scan'
]);

// Handle the scanned barcode
$router->addGet('/diary/{date}/food/lookup-barcode/{mealTypeId}', [
    'controller' => 'food',
    'action'     => 'lookupBarcode'
]);

// Template Routes
$router->addPost('/templates/save-from-diary', [
    'controller' => 'template',
    'action'     => 'saveFromDiary'
]);

$router->addPost('/templates/log', [
    'controller' => 'template',
    'action'     => 'logTemplate'
]);

$router->addGet('/templates/{id}/details', [
    'controller' => 'template',
    'action'     => 'details'
]);

$router->addPost('/templates/{id}/delete', [
    'controller' => 'template',
    'action'     => 'delete'
]);