<?php

namespace app\models\accounting;

use app\models\member\Status;
use app\modules\admin\models\FeeType;

class StatusManagerAssessment extends StatusManager
{

    /**
     * @param AssessmentAllocation $alloc
     * @return array
     */
    public function applyAssessment(AssessmentAllocation $alloc)
    {
        $errors = [];
        if ($alloc->applyToAssessment()) {
            if (!$alloc->save()) {
                $errors = $alloc->errors;
            }
        }
        if (($alloc->fee_type == FeeType::TYPE_CC) || ($alloc->fee_type == FeeType::TYPE_REINST)) {
            $member = $alloc->member;
            $received_dt = $alloc->allocatedMember->receipt->received_dt;
            $status = $this->prepareStatus($member, $received_dt);
            if ($alloc->fee_type == FeeType::TYPE_CC) {
                $status->member_status = Status::INACTIVE;
                $status->reason = isset($alloc->allocatedMember->otherLocal) ? Status::REASON_CCG . $alloc->allocatedMember->otherLocal->other_local : 'CCG';
            } else { // assume FeeType::TYPE_REINST
                $status->member_status = Status::ACTIVE;
                $status->reason = Status::REASON_REINST;
            }
            $status->alloc_id = $alloc->id;
            if (!$member->addStatus($status))
                $errors = array_merge($errors, $status->errors);
        }

        return $errors;
    }

}