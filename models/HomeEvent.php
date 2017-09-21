<?php

namespace app\models;

use Yii;
use app\helpers\OptionHelper;
use app\components\utilities\OpDate;
use app\models\user\User;

/**
 * This is the model class for table "HomeEvents".
 *
 * @property integer $id
 * @property string $title
 * @property string $all_day
 * @property string $start_dt
 * @property string $end_dt
 * @property integer $created_by
 * 
 * @property User $createdBy
 */
class HomeEvent extends \yii\db\ActiveRecord
{
	
	public $start_dt_part;
	public $start_tm_part;
	public $all_day_ckbox;
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'HomeEvents';
    }

    public function behaviors()
	{
		return [
				['class' => \yii\behaviors\BlameableBehavior::className(), 'updatedByAttribute' => false],
		];
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'start_dt_part'], 'required'],
            [['start_dt_part', 'end_dt'], 'date', 'format' => 'php:Y-m-d'],
        	[['start_tm_part'], 'time', 'format' => 'php:H:i'],
        	['duration', 'default', 'value' => 60],
            [['title'], 'string', 'max' => 50],
        	[['created_by'], 'integer'],
        	['all_day_ckbox', 'default', 'value' => '0'],	
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'all_day_ckbox' => 'All Day',
            'start_dt_part' => 'Start Date',
        	'start_tm_part' => 'Start Time',
            'end_dt' => 'End Date',
            'created_by' => 'Entered by',
        ];
    }
    
    public function beforeSave($insert)
    {
    	if(parent::beforeSave($insert)) {
    		$date = new OpDate();
    		$date->setFromMySql($this->start_dt_part);
    		
    		$date->setHM($this->start_tm_part, false);
    		$this->start_dt = $date->getMySqlDate(false);
    		$this->all_day = ($this->all_day_ckbox == '0') ? OptionHelper::TF_FALSE : OptionHelper::TF_TRUE;
    		return true;
    	}
    	return false;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

}
