<?php

namespace app\models\member;

use Yii;
use yii\base\Model;
use yii\base\InvalidConfigException;
use app\components\utilities\OpDate;
use app\models\accounting\DuesRateFinder;
use app\models\accounting\Assessment;
use app\models\accounting\BaseAllocation;

/** 
 * Model class for managing the financial status of a member
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
	
	public function init()
	{
		if(!(isset($this->member) && ($this->member instanceof Member)))
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new \yii\base\InvalidConfigException('No member object injected');
	}

    /**
     * Determines number of dues months owed by member. If paid thru is future, then returns zero.
     *
     * @return number
     * @throws InvalidConfigException
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
     * @throws InvalidConfigException
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
     * @throws InvalidConfigException
     */
	public function getBillingDescrip()
	{
		$obligation_dt = $this->getDuesObligation();
		
		switch ($this->monthsToCurrent) {
			case 0:
				$paid_thru_dt = $this->member->duesPaidThruDtObject;
				$descrip = 'Paid thru ' . $paid_thru_dt->getMonthName(true) . ' ' . $paid_thru_dt->getYear();
				break;
			case 1:
				$descrip = $obligation_dt->getMonthName(true);
				break;
			default:
				$start_dt = clone $this->member->duesPaidThruDtObject;
				$start_dt->modify('+1 month');
				$descrip = $start_dt->getMonthName(true) . '-' . $obligation_dt->getMonthName(true) . ' (' . $this->monthsToCurrent . ' months)';
		}
		
		if ($this->getOutStandingAssessment('IN'))
			$descrip .= ' + Init Fee';
		
		return $descrip;
	}

    /**
     * Returns total outstanding dues owed. If paid thru is future, returns 0.00
     *
     * @param DuesRateFinder $rateFinder
     * @return number
     * @throws InvalidConfigException
     * @throws \yii\db\Exception
     */
	public function getDuesBalance(DuesRateFinder $rateFinder)
	{
		$obligation_dt = $this->getDuesObligation();
		$adjusted_pt_dt = clone $this->member->duesPaidThruDtObject;
		$adjusted_pt_dt->modify('+' . $this->getDiscountedMonths() . ' month');
		return ($obligation_dt > $this->member->duesPaidThruDtObject) ? $rateFinder->computeBalance($adjusted_pt_dt->getMySqlDate(), $obligation_dt->getMySqlDate()) : 0.00;
	}
	
	public function getOutstandingAssessment($fee_type)
	{
		$assessment = Assessment::findOne(['member_id' => $this->member->member_id, 'fee_type' => $fee_type]);
		return (isset($assessment) && ($assessment->balance <> 0)) ? $assessment : null;
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
     * @return \app\components\utilities\OpDate
     */
	private function getCurrentMonthEnd()
	{
		if(!isset($this->_currentMonthEnd)) {
            /** @noinspection PhpUnhandledExceptionInspection */
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
     * @see Member::getDuesStartDt
     * @return \app\components\utilities\OpDate
     * @throws InvalidConfigException
     */
	private function getDuesObligation()
	{
        /** @noinspection PhpUnhandledExceptionInspection */
        $monthend_dt = $this->getCurrentMonthEnd();
		if ($this->member->isInApplication()) {
			$apf = $this->member->currentApf;
			if (isset($apf)) {
				$obligation_dt = $this->member->getDuesStartDt();
				$obligation_dt->modify('+' . $apf->months . ' month');
				if (OpDate::dateDiff($obligation_dt, $monthend_dt) > 0)
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
