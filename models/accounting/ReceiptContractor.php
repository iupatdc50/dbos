<?php

namespace app\models\accounting;

use Yii;
use \app\models\accounting\ResponsibleEmployer;
use \app\models\contractor\Contractor;

class ReceiptContractor extends Receipt
{
	
	protected $_remit_filter = 'employer_remittable';
	/**
	 * Injected employer object
	 * @var ResponsibleEmployer
	 */
	public $responsible;
	public $fee_types = [];
	
    public function rules()
    {
        $this->_validationRules = [
        	[['fee_types'], 'safe'],
        ];
        return parent::rules();
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
    
}
