<?php

namespace app\models\accounting;

use Yii;
use yii\base\Model;
use app\models\accounting\AllocatedMember;
use app\models\accounting\BaseAllocation;
use app\models\accounting\AssessmentAllocation;
use app\models\accounting\DuesAllocation;
use app\models\member\Standing;

class AllocationBuilder extends Model
{
	private $_errors;
	
	public function prepareAllocs(AllocatedMember $memb, $fee_types = [])
	{
		foreach ($fee_types as $fee_type) {
			if($fee_type == 'DU') {
				$alloc = new DuesAllocation([
						'alloc_memb_id' => $memb->id,
						'duesRateFinder' => new DuesRateFinder(
								$memb->member->currentStatus->lob_cd,
								$memb->member->currentClass->rate_class
						),
				]);
				$alloc->allocation_amt = $alloc->estimateOwed();
			} else {
				$alloc = new AssessmentAllocation([
						'alloc_memb_id' => $memb->id,
				]);
				$standing = new Standing(['member' => $memb->member]);
				if ($assessment = $standing->getOutstandingAssessment($fee_type)) {
					$alloc->allocation_amt = $assessment->balance;
				} else {
					$fee = $this->getFee($fee_type, $memb->receipt->received_dt);
					$alloc->allocation_amt = ($fee == false) ? 0.00 : $fee;
				}
			}
			$alloc->fee_type = $fee_type;
			if (!$this->saveAlloc($alloc))
				return $this->_errors;
		}
		return true;
	}

	public function prepareAllocsFromArray(AllocatedMember $memb, $array = []) {
		$strip = ['last_nm' => 'remove', 'first_nm' => 'remove', 'report_id' => 'remove'];
		$allocs = array_diff_key($array, $strip);
		foreach ($allocs as $fee_type => $amt) {
			$alloc = new BaseAllocation([
					'alloc_memb_id' => $memb->id,
					'fee_type' => $fee_type,
					'allocation_amt' => $amt,
			]);
			if (!$this->saveAlloc($alloc))
				return $this->_errors;
		}
		return true;
	}
	
	protected function saveAlloc($alloc)
	{
		try {
			$alloc->save();
		} catch (\Exception $e) {
			$this->_errors = print_r($alloc->errors, true);
			return false;
		}
		return true;
	}
	
	/**
	 * Return any preset admin fee that matches the fee type
	 * 
	 * @param string $fee_type
	 * @param string $date  MySQL format 
	 */
	private function getFee($fee_type, $date)
	{
		$sql = "SELECT fee FROM " . AdminFee::tableName() .
  			   "  WHERE fee_type = :fee_type
    				AND effective_dt <= :date
    				AND (end_dt IS NULL OR end_dt >= :date)
    			;";
		$db = yii::$app->db;
		$cmd = $db->createCommand($sql)
				  ->bindValues([
							':fee_type' => $fee_type,
							':date' => $date,
				  ]);
		return $cmd->queryScalar();
	}
	
}