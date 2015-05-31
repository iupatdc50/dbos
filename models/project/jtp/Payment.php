<?php

namespace app\models\project\jtp;

use Yii;

/**
 * This is the model class for table "JtpPayments".
 *
 * @property integer $id
 * @property string $project_id
 * @property string $payment_dt
 * @property string $paid_amt
 * @property string $actual_hrs
 * 
 * @property Project $project
 * 
 */
class Payment extends \yii\db\ActiveRecord
{
	public $close_project;
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'JtpPayments';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payment_dt', 'paid_amt', 'actual_hrs'], 'required'],
            [['payment_dt'], 'date', 'format' => 'php:Y-m-d'],
            [['paid_amt', 'actual_hrs'], 'number'],
        	[['close_project'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'Project ID',
            'payment_dt' => 'Payment Date',
            'paid_amt' => 'Amount',
            'actual_hrs' => 'Hours Used',
        ];
    }
    
    public function getProject()
    {
    	return $this->hasOne(Project::className(), ['project_id' => 'project_id']);
    }
}
