<?php

namespace app\models\accounting;

use Yii;
use app\modules\admin\models\FeeType;

/**
 * This is the model class for table "OtherAllocations".
 *
 * @property string $assessment_id
 *
 * @property Assessment @assessment
 */
class AssessmentAllocation extends BaseAllocation
{
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
//            [['assessment_id'], 'required'],
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
    public function getAssessment()
    {
    	return $this->hasOne(Assessment::className(), ['id' => 'assessment_id']);
    }
    
    

}
