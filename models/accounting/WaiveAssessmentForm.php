<?php

namespace app\models\accounting;

use yii\base\Model;
use app\models\accounting\Assessment;
use app\models\user\User;
use Yii;

class WaiveAssessmentForm extends Model
{
	/* Injectable for testing */
	public $assessment;
	public $authUser;
	
	public $assessment_id;
	public $action_dt;
	public $authority;
	public $note;
	
	public function rules()
	{
		return [
				[['assessment_id', 'action_dt', 'authority'], 'required'],
				['action_dt', 'date', 'format' => 'php:Y-m-d'],
				['note', 'safe'],
		];
	}
	
	public function attributeLabels()
	{
		return [
			'action_dt' => 'Actioned',
			'authority' => 'Fee Type',
		];
	}
	
	public function getAssessment()
	{
		if(!$this->assessment)
			$this->assessment = Assessment::findOne($this->assessment_id);
		return $this->assessment;
	}
	
    public function getAuthUser()
    {
    	if(!$this->authUser)
    		$this->authUser = User::findOne($this->authority);
        return $this->authUser;
    }
		
}