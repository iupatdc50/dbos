<?php

namespace app\models\value;

use Yii;
use app\models\member\ClassCode;
use app\models\value\RateClass;


/**
 * This is the model class for table "BillRates".
 *
 * @property integer $id
 * @property string $lob_cd
 * @property string $member_class
 * @property string $rate_class
 * @property string $fee_type
 * @property string $effective_dt
 * @property string $end_dt
 * @property string $rate
 *
 * @property Lob $lobCd
 * @property MemberClass $memberClass
 * @property RateClass $rateClass
 * @property FeeType $feeType
 */
class BillRate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'BillRates';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lob_cd', 'member_class', 'rate_class', 'fee_type', 'effective_dt', 'rate'], 'required'],
            [['effective_dt', 'end_dt'], 'safe'],
            [['rate'], 'number'],
            [['lob_cd'], 'string', 'max' => 4],
            [['member_class', 'rate_class', 'fee_type'], 'string', 'max' => 2]
        	// member_class + rate_class combination must exist in AllowableClasses
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lob_cd' => 'Local',
            'member_class' => 'Member Class',
            'rate_class' => 'Rate Class',
            'fee_type' => 'Fee Type',
            'effective_dt' => 'Effective',
            'end_dt' => 'End',
            'rate' => 'Rate',
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
    public function getMemberClass()
    {
        return $this->hasOne(ClassCode::className(), ['member_class_cd' => 'member_class']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRateClass()
    {
        return $this->hasOne(RateClass::className(), ['rate_class' => 'rate_class']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeeType()
    {
        return $this->hasOne(FeeType::className(), ['fee_type' => 'fee_type']);
    }
}
