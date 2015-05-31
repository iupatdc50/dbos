<?php

namespace app\models\project\jtp;

use Yii;

/**
 * This is the model class for table "Registrations".
 *
 * @property string $subsidy_rate
 * 
 */
class Registration extends \app\models\project\BaseRegistration
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
            [['subsidy_rate'], 'required'],
        	[['subsidy_rate'], 'number'],
        ];
        return parent::rules();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $this->_labels = [
            'subsidy_rate' => 'Rate',
        ];
        return parent::attributeLabels();
    }

}