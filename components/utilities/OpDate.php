<?php

namespace app\components\utilities;

/**
 * Extends the standard PHP 5.2 DateTime class to override modify() and provide
 * some additional functionality
 *
 * This class handles shortcomings that may be addressed in 5.3 DateTime
 *
 * @author jmdemoor
 * @copyright Copyright (c) ObjectPac 2015
 * @version 1.5
 *         
 */
class OpDate extends \DateTime 
{
	protected $_year;
	protected $_month;
	protected $_day;
	protected $_hour;
	protected $_minute;
	protected $_second;
	// protected $_ampm;
	
	/**
	 * Calculates the number of days between 2 dates
	 *
	 * @param OpDate $date1 Start date
	 * @param OpDate $date2	End date
	 * @return int
	 */
	static public function dateDiff(OpDate $date1, OpDate $date2) {
		$start = gmmktime ( 0, 0, 0, $date1->_month, $date1->_day, $date1->_year );
		$end = gmmktime ( 0, 0, 0, $date2->_month, $date2->_day, $date2->_year );
		return ($end - $start) / (60 * 60 * 24);
	}
	
	/**
	 * Forces object creation with now date only to override how class handles
	 * incorrect dates
	 *
	 * @param DateTimeZone $timezone        	
	 */
	public function __construct($timezone = null) {
		if ($timezone) {
			parent::__construct ( 'now', $timezone );
		} else {
			parent::__construct ( 'now' );
		}
		// $this->setTime(0, 0, 0);
		$this->refreshDateParts ();
		$this->refreshTimeParts ();
	}
	
	/**
	 * Sets Date portion of DateTime
	 *
	 * Overrides DateTime::setDate() to accept only valid dates
	 *
	 * @param int $year        	
	 * @param int $month        	
	 * @param int $day        	
	 */
	public function setDate($year, $month, $day) {
		if (! is_numeric ( $year ) || ! is_numeric ( $month ) || ! is_numeric ( $day )) {
			$msg = 'Expects 3 numbers separated by commas in the order: year, month, day. ';
			throw new \yii\base\InvalidValueException ( $msg . "Submitted: Year `{$year}` Month `{$month}` Day `{$day}`" );
		}
		if (! checkdate ( $month, $day, $year )) {
			throw new \yii\base\InvalidValueException ( "Non-existent date: Year `{$year}` Month `{$month}` Day `{$day}`" );
		}
		parent::setDate ( $year, $month, $day );
		$this->refreshDateParts ();
	}
	
	/**
	 * Sets Time portion of DateTime
	 * 
	 * @param type $hour        	
	 * @param type $minute        	
	 * @param type $second        	
	 */
	public function setTime($hour, $minute, $second = 0) {
		$submitted = "Hour `{$hour}` Minute `{$minute}` Second `{$second}`";
		if (! is_numeric ( $hour ) || ! is_numeric ( $minute ) || ! is_numeric ( $second )) {
			$msg = 'Expects 2 or 3 numbers separated by comas in the order: hour, minute, second. ';
			throw new \yii\base\InvalidValueException ( $msg . 'Submitted: ' . $submitted );
		}
		$outOfRange = FALSE;
		if ($hour < 0 || $hour > 23) {
			$outOfRange = TRUE;
		}
		if ($minute < 0 || $minute > 59) {
			$outOfRange = TRUE;
		}
		if ($second < 0 || $second > 59) {
			$outOfRange = TRUE;
		}
		if ($outOfRange) {
			throw new \yii\base\InvalidValueException ( 'Invalid time: ' . $submitted );
		}
		parent::setTime ( $hour, $minute, $second );
		$this->refreshTimeParts ();
	}
	
	/**
	 * Determines of date is in a leap year
	 *
	 * @return bool
	 */
	public function isLeap() {
		if ($this->_year % 400 == 0 || ($this->_year % 4 == 0 && $this->_year != 0)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Alters the timestamp
	 *
	 * Overrides the month add and subtract functionality to more correctly handle
	 * end of month out-of-bounds.
	 *
	 * @param string $modify
	 *        	Standard strtotime add/sub expressions
	 */
	public function modify($modify) {
		$parts = explode ( ' ', $modify );
		if (! is_array ( $parts ) || count ( $parts ) != 2) {
			throw new \yii\base\InvalidValueException ( "Expecting a 2 part expression. Submitted: `{$modify}`." );
		}
		if ($parts [1] === 'month') {
			$count = 0;
			$add = str_replace ( '+', '', $parts [0], $count );
			if ($count === 1) {
				$this->addMonths ( $add );
				return;
			}
			$subtract = str_replace ( '-', '', $parts [0], $count );
			if ($count === 1) {
				$this->subtractMonths ( $subtract );
				return;
			}
			throw new \yii\base\InvalidValueException ( "Ill-formed add/substract months expression `{$modify}`." );
		}
		// Pass all non-month arithmetic to parent
		parent::modify ( $modify );
		$this->refreshDateParts ();
	}
	
	public function setToMonthEnd()
	{
		$this->modify('+1 month');
		$this->setDate($this->_year, $this->_month, 1);
		$this->modify('-1 day');
	}
	
	/**
	 * Performs setDate() with a standard US Date input
	 *
	 * @param string $dateString
	 *        	Expects MM/DD/YYYY, but also allows "-", space,
	 *        	":" or "." as separator
	 */
	public function setMDY($dateString) {
		$parts = preg_split ( '{[-/ :.]}', $dateString );
		if (! is_array ( $parts ) || count ( $parts ) != 3) {
			throw new \yii\base\InvalidValueException ( "Expects date as MM/DD/YYYY. Submitted `{$dateString}`." );
		}
		$this->setDate ( $parts [2], $parts [0], $parts [1] );
	}
	
	public function setHM($timeString, $ampm = TRUE) {
		$divs = explode ( ' ', $timeString );
		if ($ampm && count ( $divs ) != 2) {
			throw new \yii\base\InvalidValueException ( "`{$timeString}` is missing am/pm" );
		}
		$parts = explode ( ':', $divs [0] );
		if (count ( $parts ) != 2) {
			throw new \yii\base\InvalidValueException ( "Expecting time as `HH:MM`. Submitted `{$timeString}`." );
		}
		if ($ampm) {
			if ($parts [0] == 12) {
				$hour = strtolower ( $divs [1] ) == 'pm' ? $parts [0] : 00;
			} else {
				$hour = strtolower ( $divs [1] ) == 'pm' ? $parts [0] + 12 : $parts [0];
			}
		}
		$this->setTime ( $hour, $parts [1] );
	}
	
	/**
	 * Performs setDate with a standard MySQL format date input
	 *
	 * @param string $dateString
	 *        	Expects YYYY-MM-DD hh:mm:ss for ISO US English dates
	 */
	public function setFromMySql($dateString) {
		if (! is_string ( $dateString )) {
			throw new \yii\base\InvalidValueException ( "Expecting a date string" );
		}
		$divs = explode ( ' ', $dateString );
		$parts = explode ( '-', $divs [0] );
		if (! is_array ( $parts ) || count ( $parts ) != 3) {
			throw new \yii\base\InvalidValueException ( "Expecting date as `YYYY-MM-DD`. Submitted: {$dateString}" );
		}
		$this->setDate ( $parts [0], $parts [1], $parts [2] );
		if (count ( $divs ) == 2) {
			$parts = explode ( ':', $divs [1] );
			if (! is_array ( $parts ) || count ( $parts ) != 3) {
				throw new \yii\base\InvalidValueException ( "Expecting time as `HH:MM:SS`. Submitted: {$dateString}" );
			}
			$this->setTime ( $parts [0], $parts [1], $parts[2] );
		} else {
			$this->setTime (0, 0, 0);
		}
		return $this;
	}
	
	/**
	 * Returns a formatted date for display
	 *
	 * @param bool $leading
	 *        	When true, provide leading zero in month and day
	 * @param string $separator
	 *        	Default is '-'
	 * @return string MM-DD-YYYY formatted date string, unless $separator
	 *         is provided
	 */
	public function getDisplayDate($leading = true, $separator = '-') {
		return $leading ? $this->format ( "m{$separator}d{$separator}Y" ) : $this->format ( "n{$separator}j{$separator}Y" );
	}
	public function getDisplayTime($ampm = TRUE) {
		return $ampm ? $this->format ( 'h:i a' ) : $this->format ( 'H:i' );
	}
	public function getDisplayDateTime($leading = true, $separator = '-', $ampm = TRUE) {
		$dt = $this->getDisplayDate ( $leading, $separator );
		$tm = $this->getDisplayTime ( $ampm );
		$tm = $tm != '12:00 am' ? ' ' . $tm : '';
		return $dt . $tm;
	}
	
	/**
	 * Returns year portion of date
	 *
	 * @return int
	 */
	public function getYear() {
		return $this->_year;
	}
	
	/**
	 * Returns month portion of date
	 *
	 * @return int
	 */
	public function getMonth() {
		return $this->_month;
	}
	
	/**
	 * Returns day portion of date
	 *
	 * @param bool $ordinal
	 *        	When true, attach "st", "nd", "rd", or "th" suffix
	 * @return variant
	 */
	public function getDay($ordinal = false) {
		return $ordinal ? $this->format ( 'jS' ) : $this->_day;
	}
	
	/**
	 * Returns month name of date
	 *
	 * @param bool $short
	 *        	When true, returns abbreviation
	 * @return string
	 */
	public function getMonthName($short = false) {
		return $short ? $this->format ( 'M' ) : $this->format ( 'F' );
	}
	
	/**
	 * Returns day name of date
	 *
	 * @param bool $short
	 *        	When true, returns abbreviation
	 * @return string
	 */
	public function getDayName($short = false) {
		return $short ? $this->format ( 'D' ) : $this->format ( 'l' );
	}
	
	/**
	 * Returns a date string that can be used in MySQL actions
	 *
	 * @param bool $notime
	 *        	When true, returns date only
	 * @return string YYYY-MM-DD (ISO US English format)
	 */
	public function getMySqlDate($notime = TRUE) {
		return $notime ? $this->format ( 'Y-m-d' ) : $this->format ( 'Y-m-d H:i:s' );
	}
	
	/**
	 * Adjust last day of month down for shorter months
	 *
	 * This procedure modifies $_day variable so it should not be overridden. It
	 * also should not be called externally
	 */
	final protected function checkEndMonthInBounds() {
		if (! checkdate ( $this->_month, $this->_day, $this->_year )) {
			$use30 = array (
					4,
					6,
					9,
					11 
			);
			if (in_array ( $this->_month, $use30 )) {
				$this->_day = 30;
			} else {
				$this->_day = $this->isLeap () ? 29 : 28;
			}
		}
	}
	
	/**
	 * Keeps date variables synchronized with timestamp
	 */
	private function refreshDateParts() {
		$this->_year = ( int ) $this->format ( 'Y' ); // 4 digit year
		$this->_month = ( int ) $this->format ( 'n' ); // month number, no leading zero
		$this->_day = ( int ) $this->format ( 'j' ); // no leading zero
	}
	private function refreshTimeParts() {
		$this->_hour = ( int ) $this->format ( 'H' );
		$this->_minute = ( int ) $this->format ( 'i' );
		$this->_second = ( int ) $this->format ( 's' );
	}
	
	/**
	 * Replacement function for adding months to a date
	 *
	 * @param int $nbrOfMonths
	 *        	Number of months to add to date
	 */
	private function addMonths($nbrOfMonths) {
		if (! is_numeric ( $nbrOfMonths )) {
			throw new \yii\base\InvalidValueException ( "Expecting an integer. Submitted `{$nbrOfMonths}`" );
		}
		$nbrOfMonths = ( int ) $nbrOfMonths;
		$new = $this->_month + $nbrOfMonths;
		if ($new <= 12) {
			$this->_month = $new;
		} else {
			$notDecember = $new % 12;
			if ($notDecember) {
				$this->_month = $notDecember;
				$this->_year += floor ( $new / 12 );
			} else {
				// Zero remainder must be December
				$this->_month = 12;
				$this->_year += ($new / 12) - 1;
			}
		}
		$this->checkEndMonthInBounds ();
		parent::setDate ( $this->_year, $this->_month, $this->_day );
	}
	
	/**
	 * Replacement functionality for subtracting months from date
	 *
	 * @param int $nbrOfMonths
	 *        	Number of months to subtract from date
	 */
	private function subtractMonths($nbrOfMonths) {
		if (! is_numeric ( $nbrOfMonths )) {
			throw new Exception ( "Expecting an integer. Submitted `{$nbrOfMonths}`" );
		}
		$nbrOfMonths = abs ( intval ( $nbrOfMonths ) );
		$new = $this->_month - $nbrOfMonths;
		if ($new > 0) {
			$this->_month = $new;
		} else {
			$months = range ( 12, 1 );
			$new = abs ( $new );
			$pos = $new % 12;
			$this->_month = $months [$pos];
			if ($pos) {
				$this->_year -= ceil ( $new / 12 );
			} else {
				// Zero array position must be December
				$this->_year -= ceil ( $new / 12 ) + 1;
			}
		}
		$this->checkEndMonthInBounds ();
		parent::setDate ( $this->_year, $this->_month, $this->_day );
	}
}
