<?php

namespace app\models\report;

class DuesStatusForm extends DateSettingsForm
{
	
	CONST OPTION_ARREARS = 'arrears';
	CONST OPTION_INACTIVE = 'inactive';
	        		
	public $options;
	
	public function rules()
	{
		$common_rules = parent::rules();
		$class_rules = [
				['options', 'safe'],
		];
		return array_merge($common_rules, $class_rules);
	}
	
	public static function getOptions()
	{
		return [
				self::OPTION_ARREARS => 'Show Arrears Only',
				self::OPTION_INACTIVE => 'Include Inactive Members',
		];
	}
	
	
	
}