<?php

namespace app\models\accounting;

use Yii;
use app\models\accounting\DuesRateFinder;
use app\models\member\Standing;
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
	 * @var string Represents the current dues 
	 */
	public $start_dt;
			
	public function init()
	{
		if(!(isset($this->duesRateFinder) && ($this->duesRateFinder instanceof DuesRateFinder)))
			throw new \yii\base\InvalidConfigException('No dues rate finder object injected');
		if(!(isset($this->start_dt)))
			$this->start_dt = $this->allocatedMember->member->dues_paid_thru_dt;
	}
	
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
    
    public function afterSave($insert, $changedAttributes) {
    	if (parent::afterSave($insert, $changedAttributes)) {
    		if (isset($changedAttributes['allocation_amt'])) {
    			// Set an event to check outstanding init balance
    		}
    		return true;
    	} else 
    		return false;    		 
    }

    /*
    public function afterDelete() {
    	if (parent::afterDelete()) {
    		// adjust paid thru on member
    	}
    }
    */
    
	/**
	 * Uses Standing class to estimate the dues allocation for a member
	 * 
	 * @return number|NULL
	 */
    public function estimateAlloc()
    {
    	if (!isset($this->alloc_memb_id))
    		return null;
    	$standing = new Standing(['member' => $this->allocatedMember->member, 'duesRateFinder' => $this->duesRateFinder]);
    	return $standing->duesBalance;
    }

	/**
	 * Calculates number of months covered by allocation_amt
	 * 
	 * @throws \Exception
	 * @return Integer
	 */
    public function calcMonths()
    {
    	$tab = $this->allocation_amt;
    	$months = 0;
    	/* @var \yii\db\DataReader $periods */
    	$periods = $this->duesRateFinder->getRatePeriods($this->start_dt);
    	foreach ($periods as $period) {
    		if($period['max_in_period'] <= $tab) {
    			$months += $period['months_in_period'];
    			// Can't use standard substract on FP numbers
    			$tab = bcsub($tab, $period['max_in_period'], 2);
    		} else {
    			$this->unalloc_remainder = fmod($tab, $period['rate']);
    			if ($this->unalloc_remainder > 0.00)	
    				throw new \yii\base\UserException("Remainder of {$this->unalloc_remainder} exists in dues allocation. Tab: {$tab}; Rate: {$period['rate']}; Max: {$period['max_in_period']}");
    			$months += $tab / $period['rate'];
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
    public function calcPaidThru($months = NULL)
    {
    	if ($months === null)
    		$months = $this->calcMonths();
    	$paid_thru = (new OpDate)->setFromMySql($this->start_dt);
    	$paid_thru->modify('+' . $months . ' month');
    	return $paid_thru->getMySqlDate();
    }
        
}
