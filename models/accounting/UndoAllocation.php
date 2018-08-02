<?php

namespace app\models\accounting;

use Yii;

/**
 * This is the model class for table "UndoAllocations".
 *
 * @property integer $id
 * @property integer $alloc_memb_id
 * @property string $fee_type
 * @property string $allocation_amt
 * @property integer $months
 * @property string $paid_thru_dt
 * @property integer $assessment_id
 */
class UndoAllocation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'UndoAllocations';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'alloc_memb_id', 'months', 'assessment_id'], 'integer'],
            [['allocation_amt'], 'number'],
            [['paid_thru_dt'], 'safe'],
            [['fee_type'], 'string', 'max' => 2],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'alloc_memb_id' => 'Alloc Memb ID',
            'fee_type' => 'Fee Type',
            'allocation_amt' => 'Allocation Amt',
            'months' => 'Months',
            'paid_thru_dt' => 'Paid Thru Dt',
            'assessment_id' => 'Assessment ID',
        ];
    }
}
