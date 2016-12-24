<?php

namespace app\models\accounting;

use Yii;

/**
 * *** !! NOTE: THIS CLASS IS DEPRECATED !! *** Use parent class
 *  
 * Model class where fee_type = 'IN'
 * 
 * @property string $months
 * 
 */

class ApfAssessment extends Assessment
{
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		/*
		$this->_validationRules = [
				[['months'], 'number'],
		];
		*/
		return parent::rules();
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		/*
		$this->_labels = [
				'months' => 'Dues Months',
		];
		*/
		return parent::attributeLabels();
	}
	
	
}