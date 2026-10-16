<?php

namespace App\Models;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\Email as EmailValidator;

class Users extends \Phalcon\Mvc\Model
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
    protected $email;

    /**
     *
     * @var string
     */
    protected $password;

    /**
     *
     * @var integer
     */
    protected $height_cm;

    /**
     *
     * @var double
     */
    protected $current_weight_kg;

    /**
     *
     * @var string
     */
    protected $created_at;

    /**
     *
     * @var string
     */
    protected $dob;

    /**
     *
     * @var string
     */
    protected $gender;

    /**
     *
     * @var double
     */
    protected $activity_level;

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
     * Method to set the value of field email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Method to set the value of field password
     *
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Method to set the value of field height_cm
     *
     * @param integer $height_cm
     * @return $this
     */
    public function setHeightCm($height_cm)
    {
        $this->height_cm = $height_cm;

        return $this;
    }

    /**
     * Method to set the value of field current_weight_kg
     *
     * @param double $current_weight_kg
     * @return $this
     */
    public function setCurrentWeightKg($current_weight_kg)
    {
        $this->current_weight_kg = $current_weight_kg;

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
     * Method to set the value of field dob
     *
     * @param string $dob
     * @return $this
     */
    public function setDob($dob)
    {
        $this->dob = $dob;

        return $this;
    }

    /**
     * Method to set the value of field gender
     *
     * @param string $gender
     * @return $this
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Method to set the value of field activity_level
     *
     * @param double $activity_level
     * @return $this
     */
    public function setActivityLevel($activity_level)
    {
        $this->activity_level = $activity_level;

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
     * Returns the value of field email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Returns the value of field password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the value of field height_cm
     *
     * @return integer
     */
    public function getHeightCm()
    {
        return $this->height_cm;
    }

    /**
     * Returns the value of field current_weight_kg
     *
     * @return double
     */
    public function getCurrentWeightKg()
    {
        return $this->current_weight_kg;
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
     * Returns the value of field dob
     *
     * @return string
     */
    public function getDob()
    {
        return $this->dob;
    }

    /**
     * Returns the value of field gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Returns the value of field activity_level
     *
     * @return double
     */
    public function getActivityLevel()
    {
        return $this->activity_level;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'email',
            new EmailValidator(
                [
                    'model'   => $this,
                    'message' => 'Please enter a correct email address',
                ]
            )
        );

        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("mahlzeit");
        $this->setSource("users");
        $this->hasMany('id', 'App\Models\ExerciseLogs', 'user_id', ['alias' => 'ExerciseLogs']);
        $this->hasMany('id', 'App\Models\MealTypes', 'user_id', ['alias' => 'MealTypes']);
        $this->hasMany('id', 'App\Models\UserGoals', 'user_id', ['alias' => 'UserGoals']);
        $this->hasMany('id', 'App\Models\WeightLogs', 'user_id', ['alias' => 'WeightLogs']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Users[]|Users|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null): \Phalcon\Mvc\Model\ResultsetInterface
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Users|\Phalcon\Mvc\Model\ResultInterface|\Phalcon\Mvc\ModelInterface|null
     */
    public static function findFirst($parameters = null): ?\Phalcon\Mvc\ModelInterface
    {
        return parent::findFirst($parameters);
    }

}
