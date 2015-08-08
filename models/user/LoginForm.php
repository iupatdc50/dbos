<?php
namespace app\models\user;

use yii\base\Model;
use Yii;

class LoginForm extends Model
{
	public $username;
	public $password;
	public $rememberMe;

	/** @var User */
	private $_user;

	public function rules()
	{
		return [
				[['username', 'password'], 'required'],
				['rememberMe', 'boolean'],
				['password', 'validatePassword']
		];
	}

	public function validatePassword($attribute)
	{
		if ($this->hasErrors())
			return;

		$user = $this->getUser();
		if (!($user && $user->validatePassword($this->$attribute)))
			$this->addError('password', 'Incorrect username or password.');
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

	public function login()
	{
		if (!$this->validate())
			return false;

		$logged_in = Yii::$app->user->login(
				$this->getUser(),
				$this->rememberMe ? 3600 * 24 * 30 : 0
		);
								
		return $logged_in;
	}

}
