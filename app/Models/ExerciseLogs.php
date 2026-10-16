<?php

namespace App\Models;

class ExerciseLogs extends \Phalcon\Mvc\Model {

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
     * @var string
     */
    protected $date;

    /**
     *
     * @var integer
     */
    protected $activity_id;

    /**
     *
     * @var string
     */
    protected $custom_name;

    /**
     *
     * @var integer
     */
    protected $duration_minutes;

    /**
     *
     * @var integer
     */
    protected $calories_burned;

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
     * Method to set the value of field activity_id
     *
     * @param integer $activity_id
     * @return $this
     */
    public function setActivityId($activity_id)
    {
        $this->activity_id = $activity_id;

        return $this;
    }

    /**
     * Method to set the value of field custom_name
     *
     * @param string $custom_name
     * @return $this
     */
    public function setCustomName($custom_name)
    {
        $this->custom_name = $custom_name;

        return $this;
    }

    /**
     * Method to set the value of field duration_minutes
     *
     * @param integer $duration_minutes
     * @return $this
     */
    public function setDurationMinutes($duration_minutes)
    {
        $this->duration_minutes = $duration_minutes;

        return $this;
    }

    /**
     * Method to set the value of field calories_burned
     *
     * @param integer $calories_burned
     * @return $this
     */
    public function setCaloriesBurned($calories_burned)
    {
        $this->calories_burned = $calories_burned;

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
     * Returns the value of field date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Returns the value of field activity_id
     *
     * @return integer
     */
    public function getActivityId()
    {
        return $this->activity_id;
    }

    /**
     * Returns the value of field custom_name
     *
     * @return string
     */
    public function getCustomName()
    {
        return $this->custom_name;
    }

    /**
     * Returns the value of field duration_minutes
     *
     * @return integer
     */
    public function getDurationMinutes()
    {
        return $this->duration_minutes;
    }

    /**
     * Returns the value of field calories_burned
     *
     * @return integer
     */
    public function getCaloriesBurned()
    {
        return $this->calories_burned;
    }

    /**
     * Initialize method for model.
     */
    public function initialize() {
        $this->setSchema("mahlzeit");
        $this->setSource("exercise_logs");

        // ADD "App\Models\" to the target models!
        $this->belongsTo('activity_id', 'App\Models\Activities', 'id', ['alias' => 'Activities']);
        $this->belongsTo('user_id', 'App\Models\Users', 'id', ['alias' => 'Users']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ExerciseLogs[]|ExerciseLogs|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ExerciseLogs|\Phalcon\Mvc\Model\ResultInterface|\Phalcon\Mvc\ModelInterface|null
     */
    public static function findFirst($parameters = null): ?\Phalcon\Mvc\ModelInterface
    {
        return parent::findFirst($parameters);
    }

}
