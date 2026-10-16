<?php

namespace App\Models;

class MealTemplateItems extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var integer
     */
    protected $template_id;

    /**
     *
     * @var integer
     */
    protected $food_id;

    /**
     *
     * @var double
     */
    protected $quantity;

    /**
     * Method to set the value of field id
     *
     * @param integer $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Method to set the value of field template_id
     *
     * @param integer $template_id
     * @return $this
     */
    public function setTemplateId($template_id)
    {
        $this->template_id = $template_id;

        return $this;
    }

    /**
     * Method to set the value of field food_id
     *
     * @param integer $food_id
     * @return $this
     */
    public function setFoodId($food_id)
    {
        $this->food_id = $food_id;

        return $this;
    }

    /**
     * Method to set the value of field quantity
     *
     * @param double $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Returns the value of field id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the value of field template_id
     *
     * @return integer
     */
    public function getTemplateId()
    {
        return $this->template_id;
    }

    /**
     * Returns the value of field food_id
     *
     * @return integer
     */
    public function getFoodId()
    {
        return $this->food_id;
    }

    /**
     * Returns the value of field quantity
     *
     * @return double
     */
    public function getQuantity()
    {
        return $this->quantity;
    }


    public function initialize() {
        $this->setSchema("mahlzeit");
        $this->setSource("meal_template_items");
        $this->belongsTo('template_id', 'App\Models\MealTemplates', 'id', ['alias' => 'Template']);
        $this->belongsTo('food_id', 'App\Models\Foods', 'id', ['alias' => 'Food']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return MealTemplateItems[]|MealTemplateItems|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return MealTemplateItems|\Phalcon\Mvc\Model\ResultInterface|\Phalcon\Mvc\ModelInterface|null
     */
    public static function findFirst($parameters = null): ?\Phalcon\Mvc\ModelInterface
    {
        return parent::findFirst($parameters);
    }

}
