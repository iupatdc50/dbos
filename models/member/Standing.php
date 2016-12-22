<?php

namespace app\models\member;

use Yii;
use yii\base\Model;
use yii\base\InvalidConfigException;
use app\components\utilities\OpDate;
use app\models\member\Member;
use app\models\accounting\DuesRateFinder;
use app\models\accounting\Assessment;

/** 
 * Model class for managing the financial status of a member
 * 
 * Requires the injection of a Member and a DuesRateFinder object 
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
	
	/**
	 * @var DuesRateFinder
	 */
//	public $duesRateFinder;
	
	public function init()
	{
		if(!(isset($this->member) && ($this->member instanceof Member)))
			throw new \yii\base\InvalidConfigException('No member object injected');
		/*
		if(!(isset($this->duesRateFinder) && ($this->duesRateFinder instanceof DuesRateFinder)))
			throw new \yii\base\InvalidConfigException('No dues rate finder object injected');
			*/
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
		$i = 0;
		while ($start_dt->getYearMonth() < $obligation_dt->getYearMonth()) {
			$i++;
			$start_dt->modify('+1 month');
			$start_dt->setToMonthEnd();
		}
		return $i;
	}
	
	/**
	 * Returns owed dues period into a date range
	 *   
	 * @return string
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
	 * @return number
	 */
	public function getDuesBalance(DuesRateFinder $rateFinder)
	{
		$obligation_dt = $this->getDuesObligation();
		return ($obligation_dt > $this->member->duesPaidThruDtObject) ? $rateFinder->computeBalance($this->member->dues_paid_thru_dt, $obligation_dt->getMySqlDate()) : 0.00;
	}
	
	public function getOutstandingAssessment($fee_type)
	{
		$assessment = Assessment::findOne(['member_id' => $this->member->member_id, 'fee_type' => $fee_type]);
		return (isset($assessment) && ($assessment->balance <> 0)) ? $assessment : null;
	}
	
	public function getTotalAssessmentBalance()
	{
		$sql = 'SELECT * FROM Assessments AS A WHERE member_id = :id';
		$assessments = Assessment::findBySql($sql, ['id' => $this->member->member_id])->all();
		$balance = 0.00;
		foreach($assessments as $assessment)
			$balance += $assessment->balance;
		return $balance; 
	}
	
	/**
	 * Today's date is current based on injected Member object
	 * 
	 * @returns OpDate
	 * @throws \yii\base\InvalidConfigException
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
	 */
	private function getDuesObligation()
	{
		if ($this->member->isInApplication()) {
			$apf = $this->member->currentApf;
			if (isset($apf)) {
				$obligation_dt = $this->member->getDuesStartDt();
				$obligation_dt->modify('+' . $apf->months . ' month');
			} else {
				Yii::warning("Member `{$this->member->member_id}` is in application but does not have a current APF Assessment.");
				$obligation_dt = $this->getCurrentMonthEnd();
			}
		} else {
			$obligation_dt = $this->getCurrentMonthEnd();
		}
		return $obligation_dt;
	}
	
}
