<?php

namespace app\models\member;

use Yii;

class MemberAccount extends \yii\base\Model
{
	/**
	 * Total upaid and partial assessments
	 * 
	 * @return number
	 */
	public function getOutstandingAssessment()
	{
		return 0.00;
	}
	
	/**
	 * Total computed dues owed between Member::dues_paid_thru_dt and 
	 * end of current month
	 * 
	 * @return number
	 */
	public function getDuesOwed()
	{
		return 0.00;
	}
	
	/**
	 * Total what is allocated to APF as of Member::application_dt and subtract 
	 * from assessment
	 * 
	 * @return number
	 */
	public function getInitBalance()
	{
		return 0.00;
	}
	
	
}