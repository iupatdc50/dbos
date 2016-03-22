<?php

namespace app\modules\admin\models;

use Yii;

/**
 * This is the model class for table "TradeFeeTypes".
 *
 * @property string $lob_cd
 * @property string $fee_type
 * @property string $employer_remittable
 * @property string $member_remittable
 *
 * @property Lobs $lobCd
 * @property FeeTypes $feeType
 */
class TradeFee extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'TradeFeeTypes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lob_cd', 'fee_type'], 'required'],
            [['employer_remittable', 'member_remittable'], 'string'],
            [['lob_cd'], 'string', 'max' => 4],
            [['fee_type'], 'string', 'max' => 2]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'lob_cd' => 'Local',
            'fee_type' => 'Fee Type',
            'employer_remittable' => 'Employer Remittable',
            'member_remittable' => 'Member Remittable',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLobCd()
    {
        return $this->hasOne(Lob::className(), ['lob_cd' => 'lob_cd']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeeType()
    {
        return $this->hasOne(FeeType::className(), ['fee_type' => 'fee_type']);
    }
}
