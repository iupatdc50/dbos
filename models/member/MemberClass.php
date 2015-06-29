<?php

namespace app\models\member;

use Yii;
use yii\db\Query;
use app\models\member\ClassCode;
use app\models\value\RateClass;

/**
 * This is the model class for table "MemberClasses".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $effective_dt
 * @property string $end_dt
 * @property string $member_class
 * @property string $rate_class
 * @property integer $wage_percent
 *
 * @property MemberClass $mClass
 * @property RateClass $rClass
 */
class MemberClass extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberClasses';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'effective_dt', 'member_class', 'rate_class'], 'required'],
            [['effective_dt', 'end_dt'], 'date'],
            [['wage_percent'], 'integer'],
            [['member_id'], 'exist', 'targetClass' => '\app\models\member\Member'],
            [['member_class'], 'exist', 'targetClass' => '\app\models\member\ClassCode'],
            [['rate_class'], 'string', 'max' => 2],
            [['member_id', 'effective_dt'], 'unique', 'targetAttribute' => ['member_id', 'effective_dt'], 'message' => 'The combination of Member ID and Effective Dt has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'effective_dt' => 'Effective',
            'end_dt' => 'End',
            'member_class' => 'Member Class',
            'rate_class' => 'Rate Class',
            'wage_percent' => 'Wage Percent',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMClass()
    {
        return $this->hasOne(ClassCode::className(), ['member_class_cd' => 'member_class']);
    }
    
    public function getMClassDescrip()
    {
    	return $this->mClass->descrip . ($this->member_class == 'A' ? ' [' . $this->wage_percent . '%]' : '');
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRClass()
    {
        return $this->hasOne(RateClass::className(), ['rate_class' => 'rate_class']);
    }
}
