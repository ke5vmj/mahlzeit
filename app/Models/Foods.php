<?php

namespace App\Models;

class Foods extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var string
     */
    protected $barcode;

    /**
     *
     * @var integer
     */
    protected $user_id;

    /**
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var string
     */
    protected $brand;

    /**
     *
     * @var integer
     */
    protected $calories;

    /**
     *
     * @var double
     */
    protected $protein_g;

    /**
     *
     * @var double
     */
    protected $fat_g;

    /**
     *
     * @var double
     */
    protected $carbs_g;

    /**
     *
     * @var double
     */
    protected $serving_size;

    /**
     *
     * @var string
     */
    protected $serving_unit;

    /**
     *
     * @var double
     */
    protected $fiber_g;

    /**
     *
     * @var double
     */
    protected $sugar_g;

    /**
     *
     * @var integer
     */
    protected $sodium_mg;

    /**
     *
     * @var integer
     */
    protected $cholesterol_mg;

    /**
     *
     * @var integer
     */
    protected $calcium_mg;

    /**
     *
     * @var integer
     */
    protected $iron_mg;

    /**
     *
     * @var string
     */
    protected $ingredients;

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
     * Method to set the value of field barcode
     *
     * @param string $barcode
     * @return $this
     */
    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;

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
     * Method to set the value of field name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Method to set the value of field brand
     *
     * @param string $brand
     * @return $this
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Method to set the value of field calories
     *
     * @param integer $calories
     * @return $this
     */
    public function setCalories($calories)
    {
        $this->calories = $calories;

        return $this;
    }

    /**
     * Method to set the value of field protein_g
     *
     * @param double $protein_g
     * @return $this
     */
    public function setProteinG($protein_g)
    {
        $this->protein_g = $protein_g;

        return $this;
    }

    /**
     * Method to set the value of field fat_g
     *
     * @param double $fat_g
     * @return $this
     */
    public function setFatG($fat_g)
    {
        $this->fat_g = $fat_g;

        return $this;
    }

    /**
     * Method to set the value of field carbs_g
     *
     * @param double $carbs_g
     * @return $this
     */
    public function setCarbsG($carbs_g)
    {
        $this->carbs_g = $carbs_g;

        return $this;
    }

    /**
     * Method to set the value of field serving_size
     *
     * @param double $serving_size
     * @return $this
     */
    public function setServingSize($serving_size)
    {
        $this->serving_size = $serving_size;

        return $this;
    }

    /**
     * Method to set the value of field serving_unit
     *
     * @param string $serving_unit
     * @return $this
     */
    public function setServingUnit($serving_unit)
    {
        $this->serving_unit = $serving_unit;

        return $this;
    }

    /**
     * Method to set the value of field fiber_g
     *
     * @param double $fiber_g
     * @return $this
     */
    public function setFiberG($fiber_g)
    {
        $this->fiber_g = $fiber_g;

        return $this;
    }

    /**
     * Method to set the value of field sugar_g
     *
     * @param double $sugar_g
     * @return $this
     */
    public function setSugarG($sugar_g)
    {
        $this->sugar_g = $sugar_g;

        return $this;
    }

    /**
     * Method to set the value of field sodium_mg
     *
     * @param integer $sodium_mg
     * @return $this
     */
    public function setSodiumMg($sodium_mg)
    {
        $this->sodium_mg = $sodium_mg;

        return $this;
    }

    /**
     * Method to set the value of field cholesterol_mg
     *
     * @param integer $cholesterol_mg
     * @return $this
     */
    public function setCholesterolMg($cholesterol_mg)
    {
        $this->cholesterol_mg = $cholesterol_mg;

        return $this;
    }

    /**
     * Method to set the value of field calcium_mg
     *
     * @param integer $calcium_mg
     * @return $this
     */
    public function setCalciumMg($calcium_mg)
    {
        $this->calcium_mg = $calcium_mg;

        return $this;
    }

    /**
     * Method to set the value of field iron_mg
     *
     * @param integer $iron_mg
     * @return $this
     */
    public function setIronMg($iron_mg)
    {
        $this->iron_mg = $iron_mg;

        return $this;
    }

    /**
     * Method to set the value of field ingredients
     *
     * @param string $ingredients
     * @return $this
     */
    public function setIngredients($ingredients)
    {
        $this->ingredients = $ingredients;

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
     * Returns the value of field barcode
     *
     * @return string
     */
    public function getBarcode()
    {
        return $this->barcode;
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
     * Returns the value of field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the value of field brand
     *
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Returns the value of field calories
     *
     * @return integer
     */
    public function getCalories()
    {
        return $this->calories;
    }

    /**
     * Returns the value of field protein_g
     *
     * @return double
     */
    public function getProteinG()
    {
        return $this->protein_g;
    }

    /**
     * Returns the value of field fat_g
     *
     * @return double
     */
    public function getFatG()
    {
        return $this->fat_g;
    }

    /**
     * Returns the value of field carbs_g
     *
     * @return double
     */
    public function getCarbsG()
    {
        return $this->carbs_g;
    }

    /**
     * Returns the value of field serving_size
     *
     * @return double
     */
    public function getServingSize()
    {
        return $this->serving_size;
    }

    /**
     * Returns the value of field serving_unit
     *
     * @return string
     */
    public function getServingUnit()
    {
        return $this->serving_unit;
    }

    /**
     * Returns the value of field fiber_g
     *
     * @return double
     */
    public function getFiberG()
    {
        return $this->fiber_g;
    }

    /**
     * Returns the value of field sugar_g
     *
     * @return double
     */
    public function getSugarG()
    {
        return $this->sugar_g;
    }

    /**
     * Returns the value of field sodium_mg
     *
     * @return integer
     */
    public function getSodiumMg()
    {
        return $this->sodium_mg;
    }

    /**
     * Returns the value of field cholesterol_mg
     *
     * @return integer
     */
    public function getCholesterolMg()
    {
        return $this->cholesterol_mg;
    }

    /**
     * Returns the value of field calcium_mg
     *
     * @return integer
     */
    public function getCalciumMg()
    {
        return $this->calcium_mg;
    }

    /**
     * Returns the value of field iron_mg
     *
     * @return integer
     */
    public function getIronMg()
    {
        return $this->iron_mg;
    }

    /**
     * Returns the value of field ingredients
     *
     * @return string
     */
    public function getIngredients()
    {
        return $this->ingredients;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("mahlzeit");
        $this->setSource("foods");
        $this->hasMany('id', 'App\Models\DiaryEntries', 'food_id', ['alias' => 'DiaryEntries']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Foods[]|Foods|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Foods|\Phalcon\Mvc\Model\ResultInterface|\Phalcon\Mvc\ModelInterface|null
     */
    public static function findFirst($parameters = null): ?\Phalcon\Mvc\ModelInterface
    {
        return parent::findFirst($parameters);
    }

}
