<?php

namespace app\models\project\lma;

use Yii;
use app\helpers\OptionHelper;

/**
 * This is the model class for table "Registrations".
 *
 * @property string $is_maint
 * @property string $estimate
 * @property string $estimated_hrs_to
 * @property string $estimate_to
 *
 */
class Registration extends \app\models\project\BaseRegistration
{
	public $is_maint = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
            [['estimate'], 'required'],
        	[['estimated_hrs_to'], 'integer'],
        	[['estimate', 'estimate_to'], 'number'],
        ];
        return parent::rules();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $this->_labels = [
            'estimate' => 'Estimate',
        ];
        return parent::attributeLabels();
    }
    
    public function afterFind()
    {
    	parent::afterFind();
    	$this->is_maint = ($this->project->is_maint == 'T');
    }

}