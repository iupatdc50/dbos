<?php

namespace app\models\accounting;

use Yii;
use app\models\value\FeeType;

/**
 * This is the model class for table "OtherAllocations".
 *
 * @property string $fee_type 
 * @property string $assessment_id
 *
 */
class AssessmentAllocation extends BaseAllocation
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AssessmentAllocations';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
            [['fee_type', 'assessment_id'], 'required'],
            [['fee_type'], 'exist', 'targetClass' => FeeType::className(), 'targetAttribute' => 'fee_type'],
        	[['assessment_id'], 'exist', 'targetClass' => Assessment::className(), 'targetAttribute' => 'id'],
        ];
        return parent::rules();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $this->_labels = [
            'fee_type' => 'Fee Type',
        	'assessment_id' => 'Assessment ID',
        ];
        return parent::attributeLabels();
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeeType()
    {
    	return $this->hasOne(FeeType::className(), ['fee_type' => 'fee_type']);
    }
    
    

}
