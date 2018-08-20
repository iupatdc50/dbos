<?php

namespace app\models\accounting;

use app\modules\admin\models\FeeType;

/** @noinspection PropertiesInspection */

/**
 * This is the model class for table "OtherAllocations".
 *
 * @property string $assessment_id
 * @property Assessment @assessment
 */
class AssessmentAllocation extends BaseAllocation
{


    public static function allocTypes()
    {
        return [
            FeeType::TYPE_INIT,
            FeeType::TYPE_CC,
            FeeType::TYPE_REINST,
        ];
    }

    public static function find()
    {
        return new AllocationQuery(get_called_class(), ['type' => self::allocTypes(), 'tableName' => self::tableName()]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
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
    
    public function applyToAssessment()
    {
    	$standing = $this->getStanding();
    	if ($assessment = $standing->getOutstandingAssessment($this->fee_type)) {
    		$this->assessment_id = $assessment->id;
    		return true;
    	}
    	return false;
    }

    public function backOutAssessment()
    {
        if ($this->assessment_id != null) {
            $this->assessment_id = null;
            return true;
        }
        return false;
    }
    
    

}
