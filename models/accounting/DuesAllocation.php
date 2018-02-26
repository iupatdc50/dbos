<?php

namespace app\models\accounting;

use app\components\utilities\OpDate;

/**
 * This is the model class for table "DuesAllocations".
 *
 * @property integer $months
 * @property string $paid_thru_dt
 * @property int $assessment_id [int(11)]
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
    
    public function beforeDelete()
    {
    	if (parent::beforeDelete()) {
    	    if($this->months != null) {
		    	$dt = $this->calcPaidThru($this->months, OpDate::OP_SUBTRACT);
		    	$this->member->dues_paid_thru_dt = $dt;
		    	$this->member->save();
    	    }
		    return true;
    	}
    	return false;
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
     * Calculates number of months covered by allocation_amt.  Any remainder updates
     * $unalloc_remainder property
     * 
     * @param numbetr $overage
     * @return NULL|integer
     */
    public function calcMonths($overage = 0.00)
    {
    	if (!$this->rateFinderExists())
    		return null;
    	$tab = $this->allocation_amt + $overage;
    	$months = 0;
    	$this->unalloc_remainder = 0.00;
    	/* @var \yii\db\DataReader $periods */
    	$periods = $this->duesRateFinder->getRatePeriods($this->getStartDt());
    	foreach ($periods as $period) {
    		if($period['max_in_period'] <= $tab) {
    			$months += $period['months_in_period'];
    			// Can't use standard substract on FP numbers
    			$tab = bcsub($tab, $period['max_in_period'], 2);
    		} else {
    			// Can't use standard divide or fmod on FP numbers
    			while ($period['rate'] <= $tab) {
    				$months++;
    				$tab = bcsub($tab, $period['rate'], 2);
    			}
    			$this->unalloc_remainder = $tab;
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
