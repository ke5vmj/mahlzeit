<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\DiaryEntries;
use App\Models\MealTemplates;
use App\Models\MealTemplateItems;

class TemplateController extends ControllerBase {

    /**
     * Clones an existing diary meal bucket into a reusable Template.
     */
    public function saveFromDiaryAction() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('/diary');
        }

        $userId     = $this->getCurrentUserId();
        $date       = $this->request->getPost('date', 'string');
        $mealTypeId = $this->request->getPost('meal_type_id', 'int');
        $name       = $this->request->getPost('name', 'string', 'My Custom Meal');

        // Fetch everything they logged in this meal bucket today
        $entries = DiaryEntries::find([
            'conditions' => 'user_id = :uid: AND date = :date: AND meal_type_id = :mid:',
            'bind'       => ['uid' => $userId, 'date' => $date, 'mid' => $mealTypeId]
        ]);

        if (count($entries) === 0) {
            $this->flashSession->warning("No foods to save in this meal!");
            return $this->response->redirect("/diary/{$date}");
        }

        // 1. Create the parent template
        $template = new MealTemplates();
        $template->user_id = $userId;
        $template->name    = $name;

        if ($template->save()) {
            // 2. Loop and attach the items
            foreach ($entries as $entry) {
                $item = new MealTemplateItems();
                $item->template_id = $template->id;
                $item->food_id     = $entry->getFoodId();
                $item->quantity    = $entry->getQuantity();
                $item->save();
            }
            $this->flashSession->success("Meal saved as template: '{$name}'!");
        }

        return $this->response->redirect("/diary/{$date}");
    }

    /**
     * Dumps all items from a template into the user's diary.
     */
    public function logTemplateAction() {
        if (!$this->request->isPost()) {
            return $this->response->redirect('/diary');
        }

        $userId     = $this->getCurrentUserId();
        $date       = $this->request->getPost('date', 'string');
        $mealTypeId = $this->request->getPost('meal_type_id', 'int');
        $templateId = $this->request->getPost('template_id', 'int');

        // Verify ownership
        $template = MealTemplates::findFirst([
            'conditions' => 'id = :tid: AND user_id = :uid:',
            'bind'       => ['tid' => $templateId, 'uid' => $userId]
        ]);

        if (!$template) {
            $this->flashSession->error("Template not found.");
            return $this->response->redirect("/diary/{$date}/add-food/{$mealTypeId}");
        }

        $items = $template->getRelated('Items');
        $count = 0;

        foreach ($items as $item) {
            $entry = new DiaryEntries();
            $entry->assign([
                'user_id'      => $userId,
                'food_id'      => $item->food_id,
                'date'         => $date,
                'meal_type_id' => $mealTypeId,
                'quantity'     => $item->quantity
            ]);

            if($entry->save()) $count++;
        }

        $this->flashSession->success("Logged {$count} items from '{$template->name}'!");
        return $this->response->redirect("/diary/{$date}");
    }

    /**
     * Displays the contents of a saved template.
     */
    public function detailsAction(string $id) {
        $userId = $this->getCurrentUserId();

        $template = MealTemplates::findFirst([
            'conditions' => 'id = :tid: AND user_id = :uid:',
            'bind'       => ['tid' => (int) $id, 'uid' => $userId]
        ]);

        if (!$template) {
            $this->flashSession->error("Template not found.");
            return $this->response->redirect('/diary');
        }

        $this->view->template = $template;

        // Pass these through so the back button knows where to return
        $this->view->date       = $this->request->getQuery('date', 'string', date('Y-m-d'));
        $this->view->mealTypeId = $this->request->getQuery('meal_type_id', 'int', 1);
    }

    /**
     * Deletes a saved template.
     */
    public function deleteAction(string $id) {
        if (!$this->request->isPost()) {
            return $this->response->redirect('/diary');
        }

        $userId = $this->getCurrentUserId();

        $template = MealTemplates::findFirst([
            'conditions' => 'id = :tid: AND user_id = :uid:',
            'bind'       => ['tid' => (int) $id, 'uid' => $userId]
        ]);

        if ($template) {
            // Because of the foreign key constraint ('onDelete' => 'CASCADE') in your migration,
            // deleting the template will automatically delete all the MealTemplateItems inside it!
            $template->delete();
            $this->flashSession->success("Template '{$template->name}' deleted.");
        }

        $date       = $this->request->getPost('date', 'string', date('Y-m-d'));
        $mealTypeId = $this->request->getPost('meal_type_id', 'int', 1);

        // Redirect back to the Templates tab
        return $this->response->redirect("/diary/{$date}/add-food/{$mealTypeId}?tab=templates");
    }
}