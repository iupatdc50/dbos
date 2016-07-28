<?php

namespace app\models\member;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use app\models\contractor\Contractor;
use app\models\member\Members;
use app\components\utilities\OpDate;
use yii\base\InvalidCallException;

/**
 * This is the model class for table "Employment".
 *
 * @property string $member_id
 * @property string $effective_dt
 * @property string $end_dt
 * @property string $employer
 * @property string $dues_payor
 * @property string $is_loaned
 *
 * @property Member $member
 * @property Contractor $contractor
 * @property Contractor $duesPayor
 */
class Employment extends \yii\db\ActiveRecord
{
	public $member_pays;
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Employment';
    }
    
    /**
     * Returns a set of members for Select2 picklist. Full name
     * is returned as text (id, text are required columns for Select2)
     * 
     * The MySQL view includes those loaned to the employer column
     * 
     * @param string|array $search Criteria used for partial member list. If an array, then member
     * 							   key will be a like search
     */
    public static function listEmployees($search)
    {
    	if (!isset($search['employer']))
    		throw InvalidCallException('This function requires an employer parameter');
    	/* @var Query $query */
    	$query = new Query;
    	$query->select('member_id as id, full_nm as text')
    		->from('CurrentEmployeePickList')
    		->limit(10)
    		->distinct();
    	if (ArrayHelper::isAssociative($search)) { 
    		if (isset($search['full_nm'])) {
    			$query->where(['like', 'full_nm', $search['full_nm']]);
    			unset($search['full_nm']);
    		}
    		$query->andWhere($search);
    	} elseif (!is_null($search)) 
    		$query->where(['like', 'full_nm', $search]);
    	$command = $query->createCommand();
    	return $command->queryAll();
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'effective_dt', 'employer'], 'required'],
            [['effective_dt', 'end_dt'], 'date', 'format' => 'php:Y-m-d'],
            [['member_id'], 'exist', 'targetClass' => '\app\models\member\Member'],
            [['employer', 'dues_payor'], 'exist', 'targetClass' => '\app\models\contractor\Contractor', 'targetAttribute' => 'license_nbr'],
        	[['member_pays'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'effective_dt' => 'Effective',
            'end_dt' => 'End',
            'employer' => 'Employer',
            'dues_payor' => 'Dues Payor',
        ];
    }
    
    public function beforeSave($insert)
    {
    	if (parent::beforeSave($insert))
    	{
    		if (($insert) && !($this->member_pays) && !($this->is_loaned))
    			$this->dues_payor = $this->employer;
    		return true;
    	}
    	return false;
    }
    
    public function afterSave($insert, $changedAttributes)
    {
    	if ($insert) 
    		$this->closePrevious();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContractor()
    {
        return $this->hasOne(Contractor::className(), ['license_nbr' => 'employer']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDuesPayor()
    {
        return $this->hasOne(Contractor::className(), ['license_nbr' => 'dues_payor']);
    }
    
    public function getDescrip()
    {
    	if (is_null($this->end_dt)) {
    		$employer = ($this->is_loaned == 'T') ? $this->duesPayor->contractor . ' [On Loan]' : $this->contractor->contractor;
    	} else {
    		$employer = 'Unemployed ('. $this->end_dt .')';
    	}
    	return $employer;
    }
    
    protected function closePrevious()
    {
    	/* @var $end_dt OpDate */
    	$end_dt = (new OpDate())->setFromMySql($this->effective_dt)->sub(new \DateInterval('P1D'));
    	$condition = "member_id = '{$this->member_id}' AND end_dt IS NULL AND effective_dt <> '{$this->effective_dt}'";
    	Employment::updateAll(['end_dt' => $end_dt->getMySqlDate()], $condition);
    }
}
