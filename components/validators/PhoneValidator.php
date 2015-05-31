<?php

namespace app\components\validators;

use yii\validators\Validator;

class PhoneValidator extends Validator
{
	const PHONE_PATTERN = '/^(\d{3}\-)?\d{3}\-\d{4}$/';
	const LAX_PHONE_PATTERN = '/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i';
	
	public function init()
	{
		parent::init();
		$this->message = 'Not a valid U.S. phone number.';
	}
	
	/**
	 * Server side validation assumes that phone number has already been sanitized 
	 * and formatted.
	 * 
	 * (non-PHPdoc)
	 * @see \yii\validators\Validator::validateAttribute()
	 */
	public function validateAttribute($model, $attribute) 
	{
		$subject = $model->$attribute;
		$label = $model->getAttributeLabel($attribute);
		if (!preg_match(self::PHONE_PATTERN, $subject)) {
			$this->addError($model, $attribute, $this->message);
		}
	}
	
	/**
	 * Allows client side validation.  Numeric part of phone number must be
	 * either 7 or 10 digits.
	 * 
	 * (non-PHPdoc)
	 * @see \yii\validators\Validator::clientValidateAttribute()
	 */
	public function clientValidateAttribute($model, $attribute, $view)
	{
		$message = json_encode($this->message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		return <<<JS
var sanPhone = (""+value).replace(/\D/g, '');
if (!(sanPhone.length == 7 || sanPhone.length == 10) ) {
	messages.push($message);	
}
JS;
	}
}