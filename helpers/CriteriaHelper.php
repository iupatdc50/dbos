<?php

namespace app\helpers;

use Yii;

class CriteriaHelper
{
	CONST TOKEN_NOTSET = '_';
	
	public static function parseMixed($column, $value)
	{
		if (preg_match('/^(?:\s*(<>|<=|>=|<|>|=))?(.*)$/', $value, $matches))
		{
			$value = $matches[2];
			$op = $matches[1];
		}
		$criteria = ($op <> '') ? [$matches[1], $column, $matches[2]] : [$column => $value]; 
		return $criteria;
	}
}