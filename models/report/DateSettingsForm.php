<?php

namespace app\models\report;

use Yii;
use app\components\utilities\OpDate;

class DateSettingsForm extends BaseSettingsForm
{
	CONST RANGE_SEPARATOR = ' thru ';
	
	public $date_range;
	public $begin_dt;
	public $end_dt;
		
	public function rules()
	{
		$common_rules = parent::rules();
		$class_rules = [
			['date_range', 'required'],
		];
		return array_merge($common_rules, $class_rules);
	}
	
	public function attributeLabels()
	{
		$common_labels = parent::attributeLabels();
		$class_labels = [
				'date_range' => 'Reporting Period',
		];
		return array_merge($common_labels, $class_labels);
	}
	
	public function afterValidate()
	{
		parent::afterValidate();
		$dates = explode(self::RANGE_SEPARATOR, $this->date_range);
		$this->begin_dt = new OpDate();
		$this->begin_dt->setMDY($dates[0]);
		$this->end_dt = new OpDate();
		$this->end_dt->setMDY($dates[1]);
	}
	
}