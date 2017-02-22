<?php

namespace app\components\validators;

use yii\validators\Validator;

class SsnValidator extends Validator
{
	const SSN_PATTERN = '/^(\d{3}\-)?\d{2}\-\d{4}$/';
	
	public function init()
	{
		parent::init();
		$this->message = 'Not a valid SSN.';
	}
	
	/**
	 * Server side validation assumes that SSN has already been sanitized 
	 * and formatted.
	 * 
	 * (non-PHPdoc)
	 * @see \yii\validators\Validator::validateAttribute()
	 */
	public function validateAttribute($model, $attribute) 
	{
		$subject = $model->$attribute;
		$label = $model->getAttributeLabel($attribute);
		if (!preg_match(self::SSN_PATTERN, $subject)) {
			$this->addError($model, $attribute, $this->message);
		}
	}
	
	/**
	 * Allows client side validation.  Numeric part of SSN must be
	 * 9 digits.
	 * 
	 * (non-PHPdoc)
	 * @see \yii\validators\Validator::clientValidateAttribute()
	 */
	public function clientValidateAttribute($model, $attribute, $view)
	{
		$message = json_encode($this->message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		return <<<JS
var sanSsn = (""+value).replace(/\D/g, '');
if (!(sanSsn.length == 9) ) {
	messages.push($message);	
}
JS;
	}
}
