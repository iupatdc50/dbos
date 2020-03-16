<?php

namespace app\models\member;

use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;
use app\components\utilities\OpDate;
use app\models\rbac\AuthAssignment;
use kartik\password\StrengthValidator;

/**
 * This is the model class for table "Users".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property string $last_nm [varchar(30)]
 * @property string $first_nm [varchar(30)]
 * @property string $last_login [datetime]
 *
 * @property string $fullName
 *
 */
class MemberLogin extends ActiveRecord
				 implements IdentityInterface
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_CHANGE_PW = 'changepw';
	const STATUS_ACTIVE = 10;
	const STATUS_INACTIVE = 0;
	const RESET_MEMBER_PW = 'DC50-memb';
	
	public $password_clear = null;
	public $password_current;
	public $password_new;
	public $password_confirm;
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberLogins';
    }

	public function behaviors()
	{
		return [
				'timestamp' => TimestampBehavior::className(),
		];
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        	[['password_clear'], 'required', 'on' => self::SCENARIO_CREATE],
        	[['member_id', 'username', 'email'], 'required'],
            [['password_clear', 'email', 'last_nm', 'first_nm'], 'string', 'max' => 255],
            [['member_id'], 'unique', 'message' => 'Member ID is already registered'],
            [['member_id'], 'exist', 'targetClass' => Member::className()],
            [['username'], 'unique', 'message' => 'Username is already registered'],
            [['email'], 'email'],
            [['email'], 'unique', 'message' => 'Email is already registered'],
        	[['auth_key'], 'string', 'max' => 32],

        	[['password_current', 'password_new', 'password_confirm'], 'required', 'on' => self::SCENARIO_CHANGE_PW],
        	[['password_current'], 'validateCurrentPassword'],
        	[['password_new'], StrengthValidator::className(), 'preset' => 'normal', 'userAttribute' => 'username'],
        	[['password_confirm'], 'compare', 'compareAttribute' => 'password_new', 'message' => 'Passwords do not match'],
        		
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_clear' => 'Password',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        	'last_nm' => 'Last Name',
        	'first_nm' => 'First Name',

        	'password_current' => 'Current Password', 
        	'password_new' => 'New Password', 
            'password_confirm' => 'Confirm New Password', 
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws Exception
     */
    public function beforeSave($insert)
    {
        $return = parent::beforeSave($insert);
        
        if ($this->password_clear != null)
        	$this->setPassword($this->password_clear);

        if ($this->isNewRecord)
            $this->auth_key = Yii::$app->security->generateRandomString($length = 32);

        return $return;
    }

    /**
     * @param $password
     * @throws Exception
     */
    public function setPassword($password)
    {
    	$this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }
    
    /**
     * Find by member_id column
     *
     * @param string $member_id
     * @return static|NULL
     */
    public static function findByMemberId($member_id)
    {
    	return self::findOne(['member_id' => $member_id]);
    }

    /**
     * Find by username column
     *
     * @param $username
     * @return MemberLogin|null
     */
    public static function findByUsername($username)
    {
        return self::findOne(['username' => $username]);
    }
    
    /**
     * Validates password against stored hash
     *
     * @param string $password
     * @return boolean
     */
    public function validatePassword($password)
    {
    	return Yii::$app->security->validatePassword($password, $this->password_hash);
    }
    
    public function validateCurrentPassword()
    {
    	if (!$this->validatePassword($this->password_current))
    		$this->addError('password_current', 'Current password is incorrect');
    }
    
    /**
     * Tests whether the current user password is the standard temporary one
     *  
     * @return boolean
     */
    public function requiresReset()
    {
    	return $this->validatePassword(self::RESET_MEMBER_PW);
    }
    
    public function getLastLoginDisplay()
    {
    	return isset($this->last_login) ? (new OpDate)->setFromMySql($this->last_login)->getDisplayDateTime() : '(First login)';
    }
    
    // 5 methods that need to be implemented by IdentityInterface and used internally by Yii
    
    /**
     * @codeCoverageIgnore
     * @see \yii\web\IdentityInterface::getId()
     */
    public function getId()
    {
    	return $this->id;
    }
    
    public static function findIdentity($id)
    {
    	return self::findOne($id);
    }
    
    public function getAuthKey()
    {
    	return $this->auth_key;
    }
    
    public function validateAuthKey($authKey)
    {
    	return $this->getAuthKey() === $authKey;
    }

    /**
     * @param mixed $token
     * @param null $type
     * @return void|IdentityInterface
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
    	throw new NotSupportedException('You can only login by Member ID/password pair for now.');
    }
    
    public function getStatusOptions()
    {
    	return [
    			self::STATUS_ACTIVE => 'Active',
    			self::STATUS_INACTIVE => 'Disabled',
    	];
    }
    
    public function getStatusText()
    {
    	$options = $this->getStatusOptions();
    	return isset($options[$this->status]) ? $options[$this->status] : "Unknown status ({$this->status})";
    }
    
    public function getAssignments()
    {
    	return $this->hasMany(AuthAssignment::className(), ['user_id' => 'id']);
    }
    
    public function getFullName()
    {
    	return $this->last_nm . ', ' . $this->first_nm;
    }

}
