<?php

namespace app\helpers;

use Yii;

class OptionHelper
{
	CONST GENDER_FEMALE = 'F';
	CONST GENDER_MALE = 'M';
	
	CONST TF_FALSE = 'F';
	CONST TF_TRUE = 'T';
	
	CONST ADDRESS_MAILING = 'M';
	CONST ADDRESS_BILLING = 'B';
	CONST ADDRESS_LOCATION = 'L';
	CONST ADDRESS_HOME = 'H';
	
	CONST DISP_APPROVED = 'A';
	CONST DISP_DENIED = 'D';
	CONST DISP_UNDETERMINED = 'U';
	
	CONST STATUS_ACTIVE = 'A';
	CONST STATUS_CLOSED = 'C';
	CONST STATUS_CANCELLED = 'X';
	
	
	public static function getAllowedGender()
	{
		return [self::GENDER_FEMALE, self::GENDER_MALE];
	}
	
	public static function getGenderOptions()
	{
		return [
				self::GENDER_FEMALE => 'Female',
				self::GENDER_MALE => 'Male',
		];
	}
	
	public static function getGenderText($code)
	{
		$options = self::getGenderOptions();
		return isset($options[$code]) ? $options[$code] : "Unknown gender ({$code})";
	}
	
	public static function getAllowedTF()
	{
		return [self::TF_FALSE, self::TF_TRUE];
	}
	
	public static function getTFOptions()
	{
		return [
				self::TF_FALSE => 'No',
				self::TF_TRUE => 'Yes'
		];
	}
	
	public static function getTFText($code)
	{
		$options = self::getTFOptions();
		return isset($options[$code]) ? $options[$code] : "Unknown ({$code})";
	}
	
	public static function getAllowedAddressTypes()
	{
		return [self::ADDRESS_MAILING, self::ADDRESS_BILLING, self::ADDRESS_LOCATION, self::ADDRESS_HOME];
	}
	
	public static function getAddressTypeOptions()
	{
		return [
				self::ADDRESS_MAILING => 'Mailing',
				self::ADDRESS_BILLING => 'Billing',
				self::ADDRESS_LOCATION => 'Location',
				self::ADDRESS_HOME => 'Home',
		];
	}
	
	public static function getAddressTypeText($code)
	{
		$options = self::getAddressTypeOptions();
		return isset($options[$code]) ? $options[$code] : "Unknown ({$code})";
	}
	
	public static function getAllowedStatus()
	{
		return [self::STATUS_ACTIVE, self::STATUS_CLOSED, self::STATUS_CANCELLED];
	}
	
	public static function getStatusOptions()
	{
		return [
				self::STATUS_ACTIVE => 'Active',
				self::STATUS_CLOSED => 'Closed',
				self::STATUS_CANCELLED => 'Cancelled',
		];
	}
	
	public static function getStatusText($code)
	{
		$options = self::getStatusOptions();
		return isset($options[$code]) ? $options[$code] : "Unknown status ({$this->code})";
	}
	
	public static function getAllowedDisp()
	{
		return [self::DISP_APPROVED, self::DISP_DENIED, self::DISP_UNDETERMINED];
	}
	
	public static function getDispOptions()
	{
		return [
				self::DISP_APPROVED => 'Approved',
				self::DISP_DENIED => 'Denied',
				self::DISP_UNDETERMINED => 'Undetermined',
		];
	}
	
	public static function getDispText($code)
	{
		$options = self::getDispOptions();
		return isset($options[$code]) ? $options[$code] : "Unknown disposition ({$this->code})";
	}
	
	
}