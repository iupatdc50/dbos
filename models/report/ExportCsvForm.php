<?php

namespace app\models\report;

class ExportCsvForm extends DateSettingsForm
{

	CONST CELL_TILDE = "~";
	CONST CELL_COMMA = ",";
	CONST CELL_TAB = "\t";
	
	CONST ENCLOSE_NONE = '';
	CONST ENCLOSE_APOS = "'";
	CONST ENCLOSE_QUOTE = '"';
	
	public $delimiter;
	public $enclosure;

	public function rules()
	{
		$common_rules = parent::rules();
		$class_rules = [
				[['delimiter', 'enclosure'], 'required'],
		];
		return array_merge($common_rules, $class_rules);
	}

	public static function getCellOptions()
	{
		return [
				self::CELL_COMMA => 'Comma',
				self::CELL_TAB => 'Tab',
				self::CELL_TILDE => 'Tilde [~]',
		];
	}

	public static function getEncloseOptions()
	{
		return [
				self::ENCLOSE_NONE => 'None',
				self::ENCLOSE_APOS => "Apostrophe [']",
				self::ENCLOSE_QUOTE => 'Quote ["]',
		];
	}
	

}