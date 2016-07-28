<?php

namespace app\models\accounting;

use Yii;
use yii\base\Model;
use app\models\accounting\AllocatedMember;
use app\models\accounting\BaseAllocation;
use app\models\accounting\AssessmentAllocation;
use app\models\accounting\DuesAllocation;

class AllocationBuilder extends Model
{
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
				$alloc->allocation_amt = $alloc->estimateAlloc();
			} else {
				$alloc = new AssessmentAllocation([
						'alloc_memb_id' => $memb->id,
						'allocation_amt' => 0.00,
				]);
			}
			$alloc->fee_type = $fee_type;
			try {
				$alloc->save();
			} catch (\Exception $e) {
				$errors = print_r($alloc->errors, true);
				return $errors;				
			}
		}
		return true;
	}	
}