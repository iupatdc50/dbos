<?php

namespace app\models\accounting;

use app\models\member\Status;
use yii\base\InvalidConfigException;
use app\models\member\Standing;

class StatusManagerDues extends StatusManager
{
    /**
     * @var Standing
     */
    public $standing;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if(!(isset($this->standing) && ($this->standing instanceof Standing)))
            throw new InvalidConfigException('No standing object injected');
    }

    /**
     * @param DuesAllocation $alloc
     * @return array
     * @throws InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function applyDues(DuesAllocation $alloc)
    {
        $errors = [];
        $member = $this->standing->member;
        $alloc->months = $alloc->calcMonths($member->overage) + $this->standing->getDiscountedMonths();
        $alloc->paid_thru_dt = $alloc->calcPaidThru($alloc->months);

        if (!$alloc->save()) {
            $errors = $alloc->errors;
        } else {
            $member->dues_paid_thru_dt = $alloc->paid_thru_dt;
            $member->overage = $alloc->unalloc_remainder;
            if ($member->save()) {
                if ($member->isInApplication() && ($member->currentApf->balance == 0.00) && ($alloc->estimateOwed() == 0.00)) {
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
            } else {
                $errors = $member->errors;
            }
        }

        return $errors;
    }

}