<?php

namespace app\models\member;

use Yii;
use yii\base\Model;
use app\components\utilities\OpDate;
use app\models\member\Member;
use app\models\accounting\DuesRateFinder;

/** 
 * Model class for managing the financial status of a member
 * 
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
	public $duesRateFinder;
	
	public function init()
	{
		if(!(isset($this->member) && ($this->member instanceof Member)))
			throw new \yii\base\InvalidConfigException('No member object injected');
		if(!(isset($this->duesRateFinder) && ($this->duesRateFinder instanceof DuesRateFinder)))
			throw new \yii\base\InvalidConfigException('No dues rate finder object injected');
	}
	
	public function getMonthsToCurrent()
	{
		return ($this->getCurrentMonthEnd() > $this->member->duesPaidThruDtObject) 
			? $this->getCurrentMonthEnd()->diff($this->member->duesPaidThruDtObject)->format('%m') 
			: 0;
	}
	
	public function getMonthsToCurrentDescrip()
	{
		$start = $this->member->duesPaidThruDtObject;
		$end = $this->getCurrentMonthEnd();
		return $start->getMonthName(true) . ' ' . $start->getYear() . ' - ' . $end->getMonthName(true) . ' ' . $end->getYear();
	}
	
	public function getDuesBalance()
	{
		return ($this->getCurrentMonthEnd() > $this->member->duesPaidThruDtObject) 
			? $this->duesRateFinder->computeBalance($this->member->dues_paid_thru_dt, $this->getCurrentMonthEnd()->getMySqlDate()) 
			: 0.00;
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
	
	private function getCurrentMonthEnd()
	{
		if(!isset($this->_currentMonthEnd)) {
			$this->_currentMonthEnd = $this->getToday();
			$this->_currentMonthEnd->setToMonthEnd();
		}
		return $this->_currentMonthEnd;
	}
	
}
