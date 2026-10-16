<?php

namespace App\Models;

class UserGoals extends \Phalcon\Mvc\Model
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
     * @var double
     */
    protected $weekly_goal_rate_kg;

    /**
     *
     * @var double
     */
    protected $target_weight_kg;

    /**
     *
     * @var integer
     */
    protected $daily_calorie_target;

    /**
     *
     * @var integer
     */
    protected $macro_protein_pct;

    /**
     *
     * @var integer
     */
    protected $macro_fat_pct;

    /**
     *
     * @var integer
     */
    protected $macro_carbs_pct;

    /**
     *
     * @var string
     */
    protected $created_at;

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
     * Method to set the value of field weekly_goal_rate_kg
     *
     * @param double $weekly_goal_rate_kg
     * @return $this
     */
    public function setWeeklyGoalRateKg($weekly_goal_rate_kg)
    {
        $this->weekly_goal_rate_kg = $weekly_goal_rate_kg;

        return $this;
    }

    /**
     * Method to set the value of field target_weight_kg
     *
     * @param double $target_weight_kg
     * @return $this
     */
    public function setTargetWeightKg($target_weight_kg)
    {
        $this->target_weight_kg = $target_weight_kg;

        return $this;
    }

    /**
     * Method to set the value of field daily_calorie_target
     *
     * @param integer $daily_calorie_target
     * @return $this
     */
    public function setDailyCalorieTarget($daily_calorie_target)
    {
        $this->daily_calorie_target = $daily_calorie_target;

        return $this;
    }

    /**
     * Method to set the value of field macro_protein_pct
     *
     * @param integer $macro_protein_pct
     * @return $this
     */
    public function setMacroProteinPct($macro_protein_pct)
    {
        $this->macro_protein_pct = $macro_protein_pct;

        return $this;
    }

    /**
     * Method to set the value of field macro_fat_pct
     *
     * @param integer $macro_fat_pct
     * @return $this
     */
    public function setMacroFatPct($macro_fat_pct)
    {
        $this->macro_fat_pct = $macro_fat_pct;

        return $this;
    }

    /**
     * Method to set the value of field macro_carbs_pct
     *
     * @param integer $macro_carbs_pct
     * @return $this
     */
    public function setMacroCarbsPct($macro_carbs_pct)
    {
        $this->macro_carbs_pct = $macro_carbs_pct;

        return $this;
    }

    /**
     * Method to set the value of field created_at
     *
     * @param string $created_at
     * @return $this
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;

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
     * Returns the value of field weekly_goal_rate_kg
     *
     * @return double
     */
    public function getWeeklyGoalRateKg()
    {
        return $this->weekly_goal_rate_kg;
    }

    /**
     * Returns the value of field target_weight_kg
     *
     * @return double
     */
    public function getTargetWeightKg()
    {
        return $this->target_weight_kg;
    }

    /**
     * Returns the value of field daily_calorie_target
     *
     * @return integer
     */
    public function getDailyCalorieTarget()
    {
        return $this->daily_calorie_target;
    }

    /**
     * Returns the value of field macro_protein_pct
     *
     * @return integer
     */
    public function getMacroProteinPct()
    {
        return $this->macro_protein_pct;
    }

    /**
     * Returns the value of field macro_fat_pct
     *
     * @return integer
     */
    public function getMacroFatPct()
    {
        return $this->macro_fat_pct;
    }

    /**
     * Returns the value of field macro_carbs_pct
     *
     * @return integer
     */
    public function getMacroCarbsPct()
    {
        return $this->macro_carbs_pct;
    }

    /**
     * Returns the value of field created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("mahlzeit");
        $this->setSource("user_goals");
        $this->belongsTo('user_id', 'App\Models\Users', 'id', ['alias' => 'Users']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserGoals[]|UserGoals|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserGoals|\Phalcon\Mvc\Model\ResultInterface|\Phalcon\Mvc\ModelInterface|null
     */
    public static function findFirst($parameters = null): ?\Phalcon\Mvc\ModelInterface
    {
        return parent::findFirst($parameters);
    }

}
