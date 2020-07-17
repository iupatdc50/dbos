<?php

namespace app\models\employment;

use app\models\member\Member;
use app\models\member\Standing;
use app\models\value\DocumentType;
use Yii;
use yii\base\InvalidCallException;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use app\models\base\BaseEndable;
use app\models\contractor\Contractor;
use app\components\utilities\OpDate;

/**
 * This is the model class for table "Employment".
 *
 * @property string $member_id
 * @property string $employer
 * @property string $dues_payor
 * @property string $is_loaned
 * @property string $term_reason
 *
 * @property Member $member
 * @property Contractor $contractor
 * @property Contractor $duesPayor
 * @property string $descrip
 * @property Document[] $unfiledDocs
 *
 */
class Employment extends BaseEndable
{
	const TERM_CONTRACTOR = 'C';
	const TERM_MEMBER = 'M';
	const TERM_NOHOURS = 'H';
	
	/**
	 * @var Standing 	May be injected, if required
	 */
	private $_standing;
	public $loan_ckbox;

	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Employment';
    }
    
	public static function qualifier() 
	{
		return 'member_id';
	}
	
	public static function getAllowedTermReasons()
	{
		return [
				self::TERM_CONTRACTOR,
				self::TERM_MEMBER,
				self::TERM_NOHOURS,
		];
	}
	
	public static function getTermReasonOptions()
	{
		return [
				self::TERM_CONTRACTOR => 'by Contractor',
				self::TERM_MEMBER => 'by Member',
				self::TERM_NOHOURS => 'No Hours',
		];
	}


    /**
     * Returns a set of members for Select2 picklist. Full name
     * is returned as text (id, text are required columns for Select2)
     *
     * The MySQL view includes those loaned to the employer column
     *
     * @param string|array $search Criteria used for partial member list. If an array, then member
     *                               key will be a like search
     * @return array
     * @throws Exception
     */
    public static function listEmployees($search)
    {
    	if (!isset($search['employer']))
    		throw new InvalidCallException('This function requires an employer parameter');

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
    
    public static function findCurrentEmployer($member_id)
    {
    	return self::findOne(['member_id' => $member_id, 'end_dt' => null]);
    }
    
    public static function findEmployerByDate($member_id, $effective_dt)
    {
    	return self::findOne(['member_id' => $member_id, 'effective_dt' => $effective_dt]);
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
        	[['loan_ckbox', 'term_reason'], 'safe'],
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
            'dues_payor' => 'Fees Payor',
        	'loan_ckbox' => 'Loaned Out',
        ];
    }
    
    public function afterFind()
    {
    	$this->loan_ckbox = ($this->is_loaned == 'T');
    }
    
    public function beforeSave($insert)
    {
    	if (parent::beforeSave($insert))
    	{
    		if ($insert) { 
    			if ($this->is_loaned == 'F')
    				$this->dues_payor = $this->employer;
    		} else {  // assume update
    			if (($this->loan_ckbox == '0') && ($this->is_loaned == 'T')) {
    				$this->is_loaned = 'F';
    				$this->dues_payor = $this->employer;
    			} elseif (($this->loan_ckbox == '1') && ($this->is_loaned == 'F')) {
    				$this->is_loaned = 'T';
    			}
    		}
    		return true;
    	}
    	return false;
    }
    
    /**
     * @return ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getContractor()
    {
        return $this->hasOne(Contractor::className(), ['license_nbr' => 'employer']);
    }

    /**
     * @return ActiveQuery
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
    		$dt_obj = (new OpDate)->setFromMySql($this->end_dt);
    		$employer = "Unemployed ({$dt_obj->getDisplayDate(true, '/')} {$this->getTermReasonText()})";
    	}
    	return $employer;
    }
    
    public function getStanding()
    {
    	if(!(isset($this->_standing)))
    		$this->_standing = new Standing(['member' => $this->member]);
    	return $this->_standing;
    }
    
    public function setStanding(Standing $standing)
    {
    	$this->_standing = $standing;
    }
    
    public function getTermReasonText($code = null)
    {
    	$reason = isset($code) ? $code : $this->term_reason;
    	$options = self::getTermReasonOptions();
    	return isset($options[$reason]) ? $options[$reason] : '';
    }

    /**
     * @param string $catg
     * @return array
     * @throws Exception
     */
    public function getUnfiledDocs($catg = DocumentType::CATG_EMPLOYMENT)
    {
        $sql = "SELECT doc_type "
            . "  FROM " . DocumentType::tableName()
            . "  WHERE catg = :catg  "
            . "    AND doc_type NOT IN (SELECT doc_type FROM " . Document::tableName()
            . "                            WHERE member_id = :member_id "
            . "                              AND effective_dt = :effective_dt "
            . "                         ) "
            . "  ORDER BY doc_type "
        ;
        $cmd = Yii::$app->db->createCommand($sql);
        $cmd->bindValues([
            ':member_id' => $this->member_id,
            ':effective_dt' => $this->effective_dt,
            ':catg' => $catg,
        ]);
        return $cmd->queryAll();
    }




}
