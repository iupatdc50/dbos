<?php

namespace app\models\project\lma;

use Yii;
use app\helpers\OptionHelper;

/**
 * This is the model class for table "Registrations".
 *
 * @property string $is_maint
 * @property string $estimate
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
            [['estimate'], 'required'],
        	[['estimate'], 'number'],
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

}