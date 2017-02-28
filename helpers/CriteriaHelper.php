<?php

namespace app\helpers;

use Yii;
use app\components\utilities\OpDate;

class CriteriaHelper
{
	CONST TOKEN_NOTSET = '_';
	
	public static function parseMixed($column, $value, $is_date = false)
	{
		if (preg_match('/^(?:\s*(<>|<=|>=|<|>|=))?(.*)$/', $value, $matches))
		{
			$sanitized = trim($matches[2]);
			if ($is_date && !empty($sanitized)) {
				$dt = new OpDate();
				try {
					$dt->setMDY($sanitized);
				} catch (\yii\base\InvalidValueException $e) {
					Yii::warning($e->getMessage());
					return [$column => $value];
				}
				
				$value = $dt->getMySqlDate(true);
			} else
				$value = $sanitized;
			$op = $matches[1];
		}
		$criteria = !empty($op) ? [$op, $column, $value] : [$column => $value]; 
		return $criteria;
	}
}