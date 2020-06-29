<?php
namespace app\models\user;

use yii\base\Model;

class RequestPwResetForm extends Model
{
	public $email;

	public function rules()
	{
		return [
				['email', 'required'],
				['email', 'email'],
                ['email', 'validateEmail']
		];
	}

    public function validateEmail($attribute)
    {
        if ($this->hasErrors())
            return;

        $user = User::findByEmail($this->$attribute);
        if (!isset($user))
            $this->addError('email', 'This email is not associated with a user account.');
    }

}
