<?php

namespace app\models\accounting;

use Yii;
use app\models\member\ClassCode;
use \app\components\utilities\OpDate;

/**
 * This is the model class for table "InitFees".
 *
 * @property integer $id
 * @property string $lob_cd
 * @property string $member_class
 * @property string $effective_dt
 * @property string $end_dt
 * @property string $fee
 * @property integer $dues_months
 * @property string $included
 *
 * @property Lob $lobCd
 * @property ClassCode $classCd
 */
class InitFee extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'InitFees';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lob_cd', 'member_class', 'effective_dt', 'fee', 'dues_months', 'included'], 'required'],
            [['effective_dt', 'end_dt'], 'safe'],
            [['fee'], 'number'],
            [['dues_months'], 'integer'],
            [['included'], 'string'],
            [['lob_cd'], 'string', 'max' => 4],
            [['member_class'], 'string', 'max' => 1],
            [['lob_cd', 'member_class', 'effective_dt'], 'unique', 'targetAttribute' => ['lob_cd', 'member_class', 'effective_dt'], 'message' => 'The combination of Local, Member Class and Effective has already been taken.']
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
            'effective_dt' => 'Effective',
            'end_dt' => 'End',
            'fee' => 'Fee',
            'dues_months' => 'Dues Months',
            'included' => 'Included',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLobCd()
    {
        return $this->hasOne(Lobs::className(), ['lob_cd' => 'lob_cd']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClassCd()
    {
        return $this->hasOne(ClassCode::className(), ['member_class_cd' => 'member_class']);
    }
    
    /**
     * Determines the assessment amount portion of the AFP
     * 
     * If the dues are included in the APF, the dues are substracted from the amount that will 
     * go on the assessment
     * 
     * @param DuesRateFinder $finder
     * @return float
     */
    public function getAssessmentAmount(DuesRateFinder $finder)
    {
    	$amount = $this->fee;
    	if($this->included == 'T') {
    		$amount -= ($finder->getCurrentRate($this->today->getMySqlDate()) * (float) $this->dues_months);
    	}
    	return $amount;
    }    

    /**
     * Override this function when testing with fixed date
     *
     * @return \app\components\utilities\OpDate
     */
    protected function getToday()
    {
    	return new OpDate();
    }
    
    
}
