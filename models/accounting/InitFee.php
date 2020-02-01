<?php

namespace app\models\accounting;

use app\models\base\BaseEndable;
use app\models\value\Lob;
use app\models\member\ClassCode;
use \app\components\utilities\OpDate;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "InitFees".
 *
 * @property integer $id
 * @property string $lob_cd
 * @property string $member_class
 * @property string $fee
 * @property integer $dues_months
 * @property string $included
 *
 * @property Lob $lobCd
 * @property ClassCode $classCd
 */
class InitFee extends BaseEndable
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'InitFees';
    }

    public static function qualifier()
    {
        return ['lob_cd', 'member_class'];
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
            'member_class' => 'Class',
            'effective_dt' => 'Effective',
            'end_dt' => 'End',
            'fee' => 'Fee',
            'dues_months' => 'Months',
            'included' => 'Included',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getLobCd()
    {
        return $this->hasOne(Lob::className(), ['lob_cd' => 'lob_cd']);
    }

    public function getLobOptions()
    {
        return ArrayHelper::map(Lob::find()->orderBy('short_descrip')->all(), 'lob_cd', 'descrip');
    }

    /**
     * @return ActiveQuery
     */
    public function getClassCd()
    {
        return $this->hasOne(ClassCode::className(), ['member_class_cd' => 'member_class']);
    }

    public function getClassOptions()
    {
        return ArrayHelper::map(ClassCode::find()->orderBy('descrip')->all(), 'member_class_cd', 'descrip');
    }

    /**
     * Determines the assessment amount portion of the AFP
     *
     * If the dues are included in the APF, the dues are substracted from the amount that will
     * go on the assessment
     *
     * @param DuesRateFinder $finder
     * @return float
     * @throws Exception
     */
    public function getAssessmentAmount(DuesRateFinder $finder)
    {
    	$amount = $this->fee;
    	if($this->included == 'T') {
    		$amount -= ($finder->getCurrentRate($this->getToday()->getMySqlDate()) * (float) $this->dues_months);
    	}
    	return $amount;
    }    

    /**
     * Override this function when testing with fixed date
     *
     * @return OpDate
     */
    protected function getToday()
    {
    	return new OpDate();
    }
    
    
}
