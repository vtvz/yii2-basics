<?php
namespace vtvz\basics;

use Yii;
use yii\base\Model;
use yii\base\InvalidConfigException;

/**
 * Login form
 */
class BaseLoginForm extends Model
{
    public $login;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    public function getUserClass()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // login and password are both required
            [['login', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect login or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided login and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? Yii::$app->params['session.expire'] : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[login]]
     *
     * @throws InvalidConfigException throws when UserClass not assigned
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $userClass = $this->getUserClass();
            if ($userClass === false) {
                throw new InvalidConfigException("Method getUserClass should return path to user class");
            }

            $this->_user = $userClass::findByLogin($this->login);
        }

        return $this->_user;
    }
}
