<?php

namespace app\models\accounting;

use Yii;
use yii\base\Model;
use app\models\member\Standing;
use app\modules\admin\models\FeeType;

class AllocationBuilder extends Model
{
	private $_errors;
	
	/**
	 * Generates a base set of allocation "buckets" for a receipt's allocated member
	 * 
	 * If there are dues owed or existing assessments for the fee types provided, these amounts will 
	 * be filled in
	 * 
	 * @param AllocatedMember $memb 	Member to which the allocations will apply
	 * @param array $fee_types			Allocation fee types to be generated
	 * @return string|boolean
	 */
	public function prepareAllocs(AllocatedMember $memb, $fee_types = [])
	{
		foreach ($fee_types as $fee_type) {
			if($fee_type == FeeType::TYPE_DUES) {
				$alloc = new DuesAllocation([
						'alloc_memb_id' => $memb->id,
						'duesRateFinder' => new DuesRateFinder(
								$memb->member->currentStatus->lob_cd,
								isset($memb->member->currentClass) ? $memb->member->currentClass->rate_class : 'R'
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
					$fee = AdminFee::getFee($fee_type, $memb->receipt->received_dt);
					$alloc->allocation_amt = ($fee == false) ? 0.00 : $fee;
				}
			}
			$alloc->fee_type = $fee_type;
			if (!$this->saveAlloc($alloc))
				return $this->_errors;
		}
		return true;
	}

	/**
	 * Generates a base set of allocation "buckets" from an input array
	 * 
	 * @param AllocatedMember $memb		Member to which the allocations will apply
	 * @param array $array				Data used to create the allocations
	 * @return string|boolean
	 */
	public function prepareAllocsFromArray(AllocatedMember $memb, $array = []) {
		// Specify non allocation columns to ignore 
		$strip = ['classification' => 'remove', 'last_nm' => 'remove', 'first_nm' => 'remove', 'report_id' => 'remove'];
		$allocs = array_diff_key($array, $strip);
		foreach ($allocs as $fee_type => $amt) {
		    if ($amt != 0.00) {
                $alloc = new BaseAllocation([
                    'alloc_memb_id' => $memb->id,
                    'fee_type' => $fee_type,
                    'allocation_amt' => $amt,
                ]);
                if (!$this->saveAlloc($alloc))
                    return $this->_errors;
            }
		}
		return true;
	}

	public function prepareAllocsFromModel(StagedAllocation $model, $alloc_memb_id)
    {
        foreach ($model->fee_types as $fee_type) {
            $alloc = new BaseAllocation([
                'alloc_memb_id' => $alloc_memb_id,
                'fee_type' => $fee_type,
                'allocation_amt' => $model->$fee_type,
            ]);
            if (!$this->saveAlloc($alloc))
                return $this->_errors;
        }
        return true;
    }

    public function prepareAllocMemb($receipt_id, $member_id)
    {
        $alloc_memb = new AllocatedMember(['receipt_id' => $receipt_id, 'member_id' => $member_id]);
        if (!$alloc_memb->save()) {
            Yii::$app->session->addFlash('error', 'Could not save Allocated Member. Check log for details. Code `AB050`');
            Yii::error("*** AB50 Allocated Member save error.  Messages: " . print_r($alloc_memb->errors, true));
            return false;
        }
        return $alloc_memb;
    }

	protected function saveAlloc(BaseAllocation $alloc)
	{
		try {
			$alloc->save();
		} catch (\Exception $e) {
			$this->_errors = print_r($alloc->errors, true);
			return false;
		}
		return true;
	}
	
}