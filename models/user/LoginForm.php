<?php
namespace app\models\user;

use yii\base\Model;
use Yii;

class LoginForm extends Model
{
	public $username;
	public $password;
	public $rememberMe;

	/** @var UserRecord */
	public $user;

	public function rules()
	{
		return [
				[['username', 'password'], 'required'],
				['rememberMe', 'boolean'],
				['password', 'validatePassword']
		];
	}

	public function validatePassword($attributeName)
	{
		if ($this->hasErrors())
			return;

		$user = $this->getUser($this->username);
		if (!($user and $this->isCorrectHash($this->$attributeName, $user->password_hash)))
			$this->addError('password', 'Incorrect username or password.');
	}

	private function getUser($username)
	{
		if (!$this->user)
			$this->user = $this->fetchUser($username);

		return $this->user;
	}

	private function fetchUser($username)
	{
		return UserRecord::findOne(compact('username'));
	}

	private function isCorrectHash($plaintext, $hash)
	{
		return Yii::$app->security->validatePassword($plaintext, $hash);
	}

	public function login()
	{
		if (!$this->validate())
			return false;

		$user = $this->getUser($this->username);
		if (!$user)
			return false;

		$logged_in = Yii::$app->user->login(
				$user,
				$this->rememberMe ? 3600 * 24 * 30 : 0
		);
				
		return $logged_in;
	}

}
