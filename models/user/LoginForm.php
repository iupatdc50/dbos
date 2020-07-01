<?php
namespace app\models\user;

use himiklab\yii2\recaptcha\ReCaptchaValidator2;
use yii\base\Model;
use Yii;

class LoginForm extends Model
{
	public $username;
	public $password;
	public $rememberMe;
	public $reCaptcha;

	/** @var User */
	private $_user;

	public function rules()
	{
		return [
				[['username', 'password'], 'required'],
				['rememberMe', 'boolean'],
				['password', 'validatePassword'],
                [['reCaptcha'], ReCaptchaValidator2::className(), 'uncheckedMessage' => 'Please confirm that you are not a bot.'],
		];
	}

	public function validatePassword($attribute)
	{
		if ($this->hasErrors())
			return;

		$user = $this->getUser();
		if (!($user && $user->validatePassword($this->$attribute)))
			$this->addError('password', 'Incorrect username or password.');
        elseif ($user && $user->resetTokenExpired())
            $this->addError('password', 'Reset token expired.');
	}

	/**
	 * Finds User by [[username]]
	 * 
	 * @return User|null
	 */
	public function getUser()
	{
		if (!$this->_user)
			$this->_user = User::findByUsername($this->username);

		return $this->_user;
	}

    /**
     * @return bool  Returns true if login successful
     */
	public function login()
	{
		if (!$this->validate())
			return false;

		return Yii::$app->user->login(
				$this->getUser(),
				$this->rememberMe ? 3600 * 24 * 30 : 0
		);
								
	}

}
