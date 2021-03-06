<?php

namespace app\models\member;

use app\models\accounting\FeeCalendar;
use app\modules\admin\models\FeeType;
use Yii;
use yii\base\Model;
use yii\base\InvalidConfigException;
use app\components\utilities\OpDate;
use app\models\accounting\Assessment;
use app\models\accounting\BaseAllocation;

/** 
 * Model class for determining the financial status of a member
 * 
 * Requires the injection of a Member 
 */
class Standing extends Model
{
	/**
	 * @var OpDate
	 */
	private $_today;
	/**
	 * @var OpDate
	 */
	private $_currentMonthEnd;
	
	/**
	 * @var Member
	 */
	public $member;
	// Can inject for testing
	public $lob_cd;
	public $rate_class;

    /**
     * @var boolean When true, standing calculations will be based on APF only
     */
	public $apf_only;

    /**
     * @throws InvalidConfigException
     */
	public function init()
	{
		if(!(isset($this->member) && ($this->member instanceof Member)))
            throw new InvalidConfigException('No member object injected');
        $this->lob_cd = isset($this->member->currentStatus) ? $this->member->currentStatus->lob_cd : null;
        $this->rate_class = isset($this->member->currentClass) ? $this->member->currentClass->rate_class : 'R';
	}

    /**
     * Determines number of dues months owed by member. If paid thru is future, then returns zero.
     *
     * @return number
     */
	public function getMonthsToCurrent()
	{
		$start_dt = clone $this->member->duesPaidThruDtObject;
		$obligation_dt = $this->getDuesObligation();
		return $this->calcMonths($start_dt, $obligation_dt);
	}

    /**
     * Compute discounted months for granted in service card
     *
     * @return number
     */
	public function getDiscountedMonths()
	{
		$months = 0;
		$status = $this->member->inServicePeriod;
		if (isset($status)) {
			$start_dt = ($status->effective_dt > $this->member->dues_paid_thru_dt) ? $status->effective_dt : $this->member->dues_paid_thru_dt;
			$start_dt_obj = (new OpDate)->setFromMySql($start_dt);
			$end_dt_obj = ($status->end_dt === null) ? $this->getDuesObligation() : (new OpDate)->setFromMySql($status->end_dt); 
			$months = $this->calcMonths($start_dt_obj, $end_dt_obj);
		}
		return $months;
	}

    /**
     * Returns owed dues period into a date range
     *
     * @return string
\     */
	public function getBillingDescrip()
	{
		$obligation_dt = $this->getDuesObligation();

		$cr = ' - ';
		
		switch ($this->getMonthsToCurrent()) {
			case 0:
				$paid_thru_dt = $this->member->duesPaidThruDtObject;
				$descrip = 'Paid thru ' . $paid_thru_dt->getMonthName(true) . ' ' . $paid_thru_dt->getYear();
				$cr = ' + ';
				break;
			case 1:
				$descrip = $obligation_dt->getMonthName(true);
				break;
			default:
				$start_dt = clone $this->member->duesPaidThruDtObject;
				$start_dt->modify('+1 month');
				$descrip = $start_dt->getMonthName(true) . '-' . $obligation_dt->getMonthName(true) . ' (' . $this->getMonthsToCurrent() . ' months)';
		}

		if($this->member->overage > 0.00)
		    $descrip .= $cr . 'Credit ' . number_format($this->member->overage, 2);
		
		if ($this->getOutStandingAssessment(FeeType::TYPE_INIT))
			$descrip .= ' + Init Fee';
		
		return $descrip;
	}

    /**
     * Returns total outstanding dues owed. If paid thru is future, returns 0.00
     *
     * @return false|float|string|null
     */
	public function getDuesBalance()
	{
		$obligation_dt = $this->getDuesObligation();
		$adjusted_pt_dt = clone $this->member->duesPaidThruDtObject;
		$adjusted_pt_dt->modify('+' . $this->getDiscountedMonths() . ' month');
        return  isset($this->lob_cd) && isset($this->rate_class) && ($obligation_dt > $this->member->duesPaidThruDtObject)
            ? FeeCalendar::getTrueDuesBalance($this->lob_cd, $this->rate_class, $adjusted_pt_dt->getMySqlDate(), null, $obligation_dt) : 0.00;
	}

    /**
     * Returns the first matching Assessment of fee type that has a balance, null if none
     *
     * @param $fee_type
     * @return Assessment|null
     */
	public function getOutstandingAssessment($fee_type)
	{
		$assessments = Assessment::findAll(['member_id' => $this->member->member_id, 'fee_type' => $fee_type]);
		foreach ($assessments as $assessment) {
		    if ($assessment->balance <> 0)
		        return $assessment;
        }
		return null;
	}
	
	public function getTotalAssessmentBalance()
	{
		$sql = "SELECT SUM(assessment_amt) FROM Assessments AS A WHERE member_id = :id";
		$assessments = Assessment::findBySql($sql, ['id' => $this->member->member_id])->scalar();
		$sql = <<<SQL
            select COALESCE (SUM(allocation_amt), 0.00) AS alloc
              from Allocations AS Al
                join Assessments AS A ON A.id = Al.assessment_id
              where member_id = :id;
SQL;
        $allocs = BaseAllocation::findBySql($sql, ['id' => $this->member->member_id])->scalar();
        return bcsub($assessments, $allocs, 2);
	}
	
	/**
	 * Today's date is current based on injected Member object
	 * 
	 * @returns OpDate
	 */
	private function getToday()
	{
		if(!isset($this->_today)) {
			$this->_today = $this->member->today;
		}
		return $this->_today;
	}

    /**
     * Returns the last day of the current month
     *
     * @return OpDate
     */
	private function getCurrentMonthEnd()
	{
		if(!isset($this->_currentMonthEnd)) {
            $this->_currentMonthEnd = $this->getToday();
			$this->_currentMonthEnd->setToMonthEnd();
		}
		return $this->_currentMonthEnd;
	}

    /**
     * Returns the date dues must be paid thru to meet obligation.
     *
     * If member, is in application, this date is determined by adding the APF dues months to the current application
     * date.  Otherwise, the current month end is used.
     *
     * @return OpDate
     * @see Member::getDuesStartDt
     */
	private function getDuesObligation()
	{
        $monthend_dt = $this->getCurrentMonthEnd();
		if ($this->member->isInApplication()) {
			$apf = $this->member->currentApf;
			if (isset($apf)) {
				$obligation_dt = $this->member->getDuesStartDt();
				$obligation_dt->modify('+' . $apf->months . ' month');
				if (!($this->apf_only) && (OpDate::dateDiff($obligation_dt, $monthend_dt) > 0))
					$obligation_dt = $monthend_dt;
			} else {
				Yii::warning("Member `{$this->member->member_id}` is in application but does not have a current APF Assessment.");
				$obligation_dt = $monthend_dt;
			}
		} else {
			$obligation_dt = $monthend_dt;
		}
		return $obligation_dt;
	}
	
	/**
	 * Difference in months between 2 date objects
	 * 
	 * @param OpDate $start
	 * @param OpDate $end
	 * @return number
	 */
	private function calcMonths(OpDate $start, OpDate $end)
	{
		$i = 0;
		while ($start->getYearMonth() < $end->getYearMonth()) {
			$i++;
			$start->modify('+1 month');
			$start->setToMonthEnd();
		}
		return $i;
		
	}
	
}
