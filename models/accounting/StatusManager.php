<?php

namespace app\models\accounting;

use app\models\member\Member;
use app\models\member\Status;
use yii\base\Model;
use yii\db\Exception;

/**
 * Model class for managing financial status based on remittance allocation
 *
 * Requires the injection of Standing, Allocation
 */
class StatusManager extends Model
{

    /**
     * Look for existing status to overlay to avoid date conflicts
     *
     * @param Member $member
     * @param string $date
     * @return Status
     */
    protected function prepareStatus(Member $member, $date)
    {
        if (($status = Status::findOne(['member_id' => $member->member_id, 'effective_dt' => $date])) !== null)
            return $status;
        ;
        return new Status(['effective_dt' => $date]);
    }

    /**
     * Initiates on app member if obligation met
     *
     * @param BaseAllocation $alloc
     * @return array
     * @throws Exception
     */
    protected function checkApf(BaseAllocation $alloc)
    {
        $errors = [];
        $member = $alloc->member;
        if ($member->isInApplication() && ($member->currentApf->balance == 0.00) && ($member->estimateDuesOwed(true) == 0.00)) {
            $received_dt = $alloc->allocatedMember->receipt->received_dt;
            $status = $this->prepareStatus($member, $received_dt);
            $status->member_status = Status::ACTIVE;
            $status->reason = Status::REASON_APF;
            $status->alloc_id = $alloc->id;
            $member->addStatus($status);
            $member->init_dt = $received_dt;
            if (!$member->save())
                $errors = $member->errors;
        }

        return $errors;
    }

}