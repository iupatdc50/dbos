<?php

namespace app\helpers;

class OptionHelper
{
	CONST GENDER_FEMALE = 'F';
	CONST GENDER_MALE = 'M';
	
	CONST TF_FALSE = 'F';
	CONST TF_TRUE = 'T';
    CONST TF_DECLINED = 'D';
	
	CONST ADDRESS_MAILING = 'M';
	CONST ADDRESS_LOCATION = 'L';
	CONST ADDRESS_OTHER = 'O';
	
	CONST DISP_APPROVED = 'A';
	CONST DISP_DENIED = 'D';
	CONST DISP_UNDETERMINED = 'U';
	
	CONST STATUS_ACTIVE = 'A';
	CONST STATUS_CLOSED = 'C';
	CONST STATUS_CANCELLED = 'X';

	// Stripe brand spelling
	CONST BRAND_VISA = 'Visa';
	CONST BRAND_MC = 'MasterCard';
	CONST BRAND_DISCOVER = 'Discover';
	CONST BRAND_AMEX = 'American Express';
	
	private static $MONTHS = [
			'01' => 'January',
			'02' => 'February',
			'03' => 'March',
			'04' => 'April',
			'05' => 'May',
			'06' => 'June',
			'07' => 'July',
			'08' => 'August',
			'09' => 'September',
			'10' => 'October',
			'11' => 'November',
			'12' => 'December',
	];
	
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
		return isset($options[$code]) ? $options[$code] : "Unknown gender ($code)";
	}
	
	public static function getAllowedTF($declined_option = false)
	{
		$allowed = [self::TF_FALSE, self::TF_TRUE];
        if ($declined_option)
            $allowed[] = self::TF_DECLINED;
        return $allowed;
	}
	
	public static function getTFOptions($declined_option = false)
    {
		$options = [
				self::TF_FALSE => 'No',
				self::TF_TRUE => 'Yes'
		];
        if ($declined_option)
            $options[self::TF_DECLINED] = 'Declined';
        return $options;
	}
	
	public static function getTFText($code)
	{
		$options = self::getTFOptions(true);
		return isset($options[$code]) ? $options[$code] : "Unknown ($code)";
	}
	
	public static function getAllowedAddressTypes()
	{
		return [self::ADDRESS_MAILING, self::ADDRESS_LOCATION, self::ADDRESS_OTHER];
	}
	
	public static function getAddressTypeOptions()
	{
		return [
				self::ADDRESS_MAILING => 'Mailing',
				self::ADDRESS_LOCATION => 'Location',
				self::ADDRESS_OTHER => 'Other',
		];
	}
	
	public static function getAddressTypeText($code)
	{
		$options = self::getAddressTypeOptions();
		return isset($options[$code]) ? $options[$code] : "Unknown ($code)";
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
		return isset($options[$code]) ? $options[$code] : "Unknown status ($code)";
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
		return isset($options[$code]) ? $options[$code] : "Unknown disposition ($code)";
	}
	
	public static function getPrettyMonthYear($yyyymm)
	{
		$months = self::$MONTHS;
		$month = substr($yyyymm, 4);
		return isset($months[$month]) ? $months[$month] . ' ' . substr($yyyymm, 0, 4) : "Unknown month ($yyyymm)";
	}

	public static function getBrandMaskOptions()
    {
        return [
            self::BRAND_AMEX => '**** ****** *',
            self::BRAND_DISCOVER => '**** **** **** ',
            self::BRAND_MC => '**** **** **** ',
            self::BRAND_VISA => '**** **** **** ',
        ];
    }

    public static function getBrandMask($brand)
    {
        $options = self::getBrandMaskOptions();
        return isset($options[$brand]) ? $options[$brand] : "Unknown payment method ($brand)";
    }

    public static function getBrandLogoNmOptions()
    {
        return [
            self::BRAND_AMEX => 'logo_amex.png',
            self::BRAND_DISCOVER => 'logo_discover.png',
            self::BRAND_MC => 'logo_mc.png',
            self::BRAND_VISA => 'logo_visa.png',
        ];
    }

    public static function getBrandLogoNm($brand)
    {
        $options = self::getBrandLogoNmOptions();
        return isset($options[$brand]) ? $options[$brand] : "Unknown payment method ($brand)";
    }


}