<?php

namespace app\models\project;

use Yii;
use app\models\base\BaseAddress;
use app\helpers\OptionHelper;

/**
 * This is the model class for table "ProjectAddresses".
 *
 * @property string $project_id
 *
 * @property Projects $project
 */
class Address extends BaseAddress
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ProjectAddresses';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
        	[['address_type'], 'in', 'range' => [
    			OptionHelper::ADDRESS_MAILING,
    			OptionHelper::ADDRESS_LOCATION,
        	]],
        ];
        return parent::rules();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $this->_labels = [
            'project_id' => 'Project ID',
        ];
        return parent::attributeLabels();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['project_id' => 'project_id']);
    }

    public function getAddressTypeOptions()
    {
    	return [
    			OptionHelper::ADDRESS_MAILING => 'Mailing',
    			OptionHelper::ADDRESS_LOCATION => 'Location',
    	];
    }
    
}
