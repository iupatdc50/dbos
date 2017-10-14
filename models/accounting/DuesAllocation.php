<?php

namespace app\models\accounting;

use Yii;
use app\models\accounting\DuesRateFinder;
use app\components\utilities\OpDate;
use app\models\member\app\models\member;

/**
 * This is the model class for table "DuesAllocations".
 *
 * @property integer $months
 * @property string $paid_thru_dt
 * 
 */
class DuesAllocation extends BaseAllocation
{
	
	/**
	 * @var DuesRateFinder
	 */
	public $duesRateFinder;

	public $unalloc_remainder;
	/**
	 * @var string Represents the current dues paid thru. Can be injected for testing
	 */
	public $start_dt;
	
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
            [['months'], 'integer'],
            [['paid_thru_dt'], 'date', 'format' => 'php:Y-m-d'],
        ];
        return parent::rules();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $this->_labels = [
            'months' => 'Months',
            'paid_thru_dt' => 'Paid Thru',
        ];
        return parent::attributeLabels();
    }
    
    /*
    public function afterDelete() {
    	if (parent::afterDelete()) {
    		// adjust paid thru on member
    	}
    }
    */
    
    public function beforeDelete()
    {
    	if($this->months === null) {
    		Yii::error('*** DA010 Malformed allocation record for ID: ' . $this->id);
    		throw new \yii\base\ErrorException('Problem with allocation record.  Contact tech support: [DA010] ');
    	}
    	$dt = $this->calcPaidThru($this->months, OpDate::OP_SUBTRACT);
    	$this->member->dues_paid_thru_dt = $dt;
    	$this->member->save();
    }
    
	/**
	 * Uses Standing class to estimate the dues owed for a member
	 * 
	 * @return number|NULL
	 */
    public function estimateOwed()
    {
    	if (!isset($this->alloc_memb_id) || (!$this->rateFinderExists()))
    		return null;
    	$standing = $this->getStanding();
    	return $standing->getDuesBalance($this->duesRateFinder);
    }

	/**
	 * Calculates number of months covered by allocation_amt
	 * 
	 * @throws \Exception
	 * @return Integer
	 */
    public function calcMonths()
    {
    	if (!$this->rateFinderExists())
    		return null;
    	$tab = $this->allocation_amt;
    	$months = 0;
    	/* @var \yii\db\DataReader $periods */
    	$periods = $this->duesRateFinder->getRatePeriods($this->getStartDt());
    	foreach ($periods as $period) {
    		if($period['max_in_period'] <= $tab) {
    			$months += $period['months_in_period'];
    			// Can't use standard substract on FP numbers
    			$tab = bcsub($tab, $period['max_in_period'], 2);
    		} else {
    			
/*  FP numbers are in base 2, so fmod is unreliable
    			$this->unalloc_remainder = fmod($tab, $period['rate']);
    			if ($this->unalloc_remainder != 0)	
    				throw new \yii\base\UserException("Remainder of {$this->unalloc_remainder} exists in dues allocation. Tab: {$tab}; Rate: {$period['rate']}; Max: {$period['max_in_period']}");
 */
    			$months += bcdiv($tab, $period['rate'], 2);
    			$tab = 0.00;
    		}
    		if ($tab <= 0.00)
    			break;
    	}
    	return $months;
    } 
    
    /**
     * Calculates the new dues paid thru date. 
     * 
     * @return string Paid thru date in MySql format
     */
    public function calcPaidThru($months = null, $op = null)
    {
    	if ($months === null)
    		$months = $this->calcMonths();
    	if ($op === null)
    		$op = OpDate::OP_ADD;
    	$paid_thru = (new OpDate)->setFromMySql($this->getStartDt());
    	$paid_thru->modify($op . $months . ' month');
    	$paid_thru->setToMonthEnd();
    	return $paid_thru->getMySqlDate();
    }
    
    private function getStartDt()
    {
		if(!(isset($this->start_dt)))
			$this->start_dt = $this->member->dues_paid_thru_dt;
		return $this->start_dt;
    }
    
    private function rateFinderExists()
    {
    	if (isset($this->duesRateFinder)) {
    		if(!($this->duesRateFinder instanceof DuesRateFinder))
    			throw new \yii\base\InvalidConfigException('Not a valid dues rate finder object');
    	} else
    		throw new \yii\base\InvalidConfigException('No dues rate finder object injected');
    	return true;
    }
        
}
