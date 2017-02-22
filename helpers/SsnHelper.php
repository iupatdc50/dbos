<?php

namespace app\helpers;

use Yii;

class SsnHelper
{
	
	public static function format_us ($ssn) {

		$sanitized = preg_replace("/[^0-9]/", "", $ssn);
		$length = strlen($sanitized);
		if (!($length == 9))
			return $ssn;
		
		return preg_replace("/([0-9]{3})([0-9]{2})([0-9]{4})/", "$1-$2-$3", $sanitized);
		
	}
}