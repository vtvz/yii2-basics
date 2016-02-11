<?php
namespace vtvz\basics;

use vtvz\extrelations\Relations;
use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * BaseUser class
 *
 * @property integer $id
 * @property string $passwordHash
 * @property timestamp $createdAt
 * @property timestamp $updatedAt
 * @property integer $authKey
 * @property integer $status
 */
abstract class BaseUser extends ActiveRecord implements IdentityInterface
{
    const STATUS_ACTIVE   = 1;
    const STATUS_INACTIVE = 0;

    public $statuses = [
        'inactive' => self::STATUS_INACTIVE,
        'active' => self::STATUS_ACTIVE,
    ];

    public static $queryClass = 'vtvz\basics\UserQuery';

    public static function find()
    {
        return Yii::createObject(static::$queryClass, [get_called_class()]);
    }

    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                Relations::className(),
                [
                    'class' => TimestampBehavior::className(),
                ]
            ]
        );
    }

    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => $this->statuses],
            ['login', 'unique'],
        ];
    }

    public function makeActive()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->authKey = Yii::$app->security->generateRandomString() . '_' . time();
    }


    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->passwordHash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->passwordHash);
    }

    /**
     * Finds active user by login
     *
     * @param string $login
     * @return static|null
     */
    public static function findByLogin($login)
    {
        return static::findOne(['login' => $login, 'status' => self::STATUS_ACTIVE]);
    }

    /*
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }


    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }
}
