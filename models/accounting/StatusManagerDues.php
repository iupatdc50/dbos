<?php

namespace app\models\accounting;

use yii\base\InvalidConfigException;
use app\models\member\Standing;
use yii\db\Exception;

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
     * @throws Exception
     */
    public function applyDues(DuesAllocation $alloc)
    {
        $member = $this->standing->member;
        $alloc->months = $alloc->calcMonths($member->overage) + $this->standing->getDiscountedMonths();
        $alloc->paid_thru_dt = $alloc->calcPaidThru($alloc->months);

        if (!$alloc->save()) {
            $errors = $alloc->errors;
        } else {
            $member->dues_paid_thru_dt = $alloc->paid_thru_dt;
            $member->overage = $alloc->unalloc_remainder;
            if ($member->save())
                $errors = $this->checkApf($alloc);
            else
                $errors = $member->errors;
        }

        return $errors;
    }

}