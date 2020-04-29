<?php

namespace app\helpers;

class PhoneHelper
{
	const LOCAL_AREA_CODE = '808-';
	
	public static function format_us ($phone) {

		$sanitized = preg_replace("/[^0-9]/", "", $phone);
		$length = strlen($sanitized);
		if (($length == 11) && !(substr($sanitized, 0, 1) == 1))
			return $phone;
		switch($length) {
			case 7:
				$area = self::LOCAL_AREA_CODE;
				return preg_replace("/([0-9]{3})([0-9]{4})/", "{$area}$1-$2", $sanitized);
				break;
			case 10:
				return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $sanitized);
				break;
			case 11:
				return preg_replace("/([0-9])([0-9]{3})([0-9]{3})([0-9]{4})/", "$2-$3-$4", $sanitized);
				break;
			default:
				return $phone;
				break;
		}
		
	}
}