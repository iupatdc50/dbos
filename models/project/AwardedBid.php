<?php

namespace app\models\project;

use Yii;

/**
 * This is the model class for table "AwardedBids".
 *
 * @property string $project_id
 * @property integer $registration_id
 * @property string $start_dt
 */
class AwardedBid extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AwardedBids';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'registration_id', 'start_dt'], 'required'],
            [['registration_id'], 'integer'],
            [['start_dt'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'project_id' => 'Project ID',
            'registration_id' => 'Registration ID',
            'start_dt' => 'Start Date',
        ];
    }
    
    public function getRegistration()
    {
    	return $this->hasOne(BaseRegistration::className(), ['id' => 'registration_id']);
    }
}
