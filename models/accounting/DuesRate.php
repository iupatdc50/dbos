<?php

namespace app\models\accounting;

use Yii;
use app\models\member\ClassCode;
use app\models\value\RateClass;


/**
 * This is the model class for table "BillRates".
 *
 * @property integer $id
 * @property string $lob_cd
 * @property string $rate_class
 * @property string $effective_dt
 * @property string $end_dt
 * @property number $rate
 *
 * @property Lob $lobCd
 * @property RateClass $rateClass
 * @property FeeType $feeType
 */
class DuesRate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'DuesRates';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lob_cd', 'rate_class', 'effective_dt', 'rate'], 'required'],
            [['effective_dt', 'end_dt'], 'safe'],
            [['rate'], 'number'],
            [['lob_cd'], 'string', 'max' => 4],
            [['rate_class'], 'string', 'max' => 2],
            [['lob_cd', 'rate_class', 'effective_dt'], 'unique', 'targetAttribute' => ['lob_cd', 'rate_class', 'effective_dt'], 'message' => 'The combination of Local, Rate Class and Effective has already been taken.']
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
            'rate_class' => 'Rate Class',
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
    public function getRateClass()
    {
        return $this->hasOne(RateClass::className(), ['rate_class' => 'rate_class']);
    }
    
}
