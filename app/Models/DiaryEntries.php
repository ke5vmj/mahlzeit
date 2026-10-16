<?php

namespace App\Models;

class DiaryEntries extends \Phalcon\Mvc\Model
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
    protected $user_id;

    /**
     *
     * @var integer
     */
    protected $meal_type_id;

    /**
     *
     * @var integer
     */
    protected $food_id;

    /**
     *
     * @var string
     */
    protected $date;

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
     * Method to set the value of field user_id
     *
     * @param integer $user_id
     * @return $this
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Method to set the value of field meal_type_id
     *
     * @param integer $meal_type_id
     * @return $this
     */
    public function setMealTypeId($meal_type_id)
    {
        $this->meal_type_id = $meal_type_id;

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
     * Method to set the value of field date
     *
     * @param string $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;

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
     * Returns the value of field user_id
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Returns the value of field meal_type_id
     *
     * @return integer
     */
    public function getMealTypeId()
    {
        return $this->meal_type_id;
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
     * Returns the value of field date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
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

    /**
     * Initialize method for model.
     */
    public function initialize() {
        $this->setSchema("mahlzeit");
        $this->setSource("diary_entries");

        // Add the full namespace to the 2nd parameter
        $this->belongsTo('food_id', 'App\Models\Foods', 'id', ['alias' => 'Foods']);
        $this->belongsTo('meal_type_id', 'App\Models\MealTypes', 'id', ['alias' => 'MealTypes']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return DiaryEntries[]|DiaryEntries|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return DiaryEntries|\Phalcon\Mvc\Model\ResultInterface|\Phalcon\Mvc\ModelInterface|null
     */
    public static function findFirst($parameters = null): ?\Phalcon\Mvc\ModelInterface
    {
        return parent::findFirst($parameters);
    }

}
