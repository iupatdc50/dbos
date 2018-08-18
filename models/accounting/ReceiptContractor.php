<?php

namespace app\models\accounting;

use Yii;
use \app\models\accounting\ResponsibleEmployer;
use \app\models\contractor\Contractor;

class ReceiptContractor extends Receipt
{
	protected $_remit_filter = 'employer_remittable';
	protected $_customAttributes = [
			[
					'attribute' => 'helperDuesText',
					'label' => 'Helper Dues',
			],
        	[
			        'attribute' =>  'feeTypeTexts',
			        'format' => 'ntext',
			        'label' => 'Fee Types'
			],
			        				
	];

	/**
	 * Injected employer object
	 * @var ResponsibleEmployer
	 */
	public $responsible;

	public static function payorType()
    {
        return self::PAYOR_CONTRACTOR;
    }

	public static function find()
    {
        return new ReceiptQuery(get_called_class(), ['type' => self::payorType(), 'tableName' => self::tableName()]);
    }
	
    public function beforeSave($insert)
    {
    	if (parent::beforeSave($insert)) {
			if(!isset($this->responsible) && ($this->responsible instanceof ResponsibleEmployer))
				throw new \yii\base\InvalidConfigException('No responsible employer object injected');
    		if ($insert) {
    			if (isset($this->payor_nm)) {
    				$this->payor_type = self::PAYOR_OTHER;
    			} else {
    				$this->payor_type = self::PAYOR_CONTRACTOR;
    				$this->payor_nm = $this->responsible->employer->contractor;
    			}
    		}
    		return true;
    	}
    	return false;	 
    }
    
    public function getHelperDuesText()
    {
    	return ($this->helper_dues > 0.00) ? $this->helper_dues . ' (' . $this->helper_hrs . ' hours)' : null;
    }

    public function getCustomAttributes($forPrint = false)
    {
    	$attrs = $this->_customAttributes;
    	// no feeTypeTexts on printable receipt
    	if ($forPrint) {
    		unset($attrs[1]);
    	};
    	return $attrs;
    }
    
}
