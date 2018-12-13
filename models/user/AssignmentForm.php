<?php
namespace app\models\user;

use yii\base\Model;

class AssignmentForm extends Model
{
	public $staff_roles_only;
	public $staff_role;
	public $action_roles;

	/* @var $user \app\models\user\User */
	public $user;

    public function rules()
	{
		return [
				[['staff_only', 'user', 'staff_roles_only'], 'required'],
                [['staff_role', 'action_roles'], 'safe'],
		];
	}


}
