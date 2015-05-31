<?php

namespace app\models\project\lma;

use Yii;
use app\helpers\OptionHelper;

/**
 * 
 * @property string $is_maint
 */

class Project extends \app\models\project\BaseProject
{
	public $type_filter = 'LMA';
	
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
            [['is_maint'], 'required'],
        	[['is_maint'], 'in', 'range' => OptionHelper::getAllowedTF()],
        ];
        return parent::rules();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $this->_labels = [
            'is_maint' => 'Maintenance',
        ];
        return parent::attributeLabels();
    }
	
    public function getMaintText()
    {
    	return OptionHelper::getTFText($this->is_maint);
    }
    	
	
}