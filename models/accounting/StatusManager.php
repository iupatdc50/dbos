<?php

namespace app\models\accounting;

use app\models\member\Member;
use app\models\member\Status;
use yii\base\Model;

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
     * @return \yii\db\static|\app\models\member\Status
     */
    protected function prepareStatus(Member $member, $date)
    {
        if (($status = Status::findOne(['member_id' => $member->member_id, 'effective_dt' => $date])) !== null)
            return $status;
        ;
        return new Status(['effective_dt' => $date]);
    }


}