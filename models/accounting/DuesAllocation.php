<?php

namespace app\models\accounting;

use app\components\utilities\OpDate;
use app\modules\admin\models\FeeType;
use Throwable;
use yii\db\Exception;
use yii\db\Query;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;

/** @noinspection PropertiesInspection */

/**
 * This is the model class for table "DuesAllocations".
 *
 * @property integer $months
 * @property string $paid_thru_dt
 *
 */
class DuesAllocation extends BaseAllocation
{

	public $unalloc_remainder;

	/**
	 * @var string Represents the current dues paid thru. Can be injected for testing
	 */
	public $start_dt;
	// Member's current status and class values.  Can be injected for testing
	public $lob_cd;
	public $rate_class;

	public static function allocTypes()
    {
        return [FeeType::TYPE_DUES];
    }

    public static function find()
    {
        return new AllocationQuery(get_called_class(), ['type' => self::allocTypes(), 'tableName' => self::tableName()]);
    }

    /**
     * Returns a set of dues allocations for Select2 picklist. Receipt ID, amount & dues paid thru date
     * are returned as text (id, text are required columns for Select2)
     *
     * @param string|array $search Criteria used for partial receipt list. If an array, then descrip
     *                               key will be a like search
     * @return array
     * @throws Exception
     */
    public static function listAll($search)
    {
        $query = new Query;
        $query->select('id as id, descrip as text')
            ->from('DuesAllocPickList')
            ->limit(10)
            ->orderBy('id desc')
            ->distinct();
        if (ArrayHelper::isAssociative($search)) {
            if (isset($search['descrip'])) {
                $query->where(['like', 'descrip', $search['descrip']]);
                unset($search['descrip']);
            }
            $query->andWhere($search);
        } elseif (!is_null($search))
            $query->where(['like', 'descrip', $search]);
        $command = $query->createCommand();
        return $command->queryAll();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
            [['months'], 'integer'],
            [['paid_thru_dt'], 'date', 'format' => 'php:Y-m-d'],
        ];
        return parent::rules();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $this->_labels = [
            'months' => 'Months',
            'paid_thru_dt' => 'Paid Thru',
        ];
        return parent::attributeLabels();
    }

    /**
     * @return bool
     * @throws StaleObjectException
     * @throws Exception
     * @throws Throwable
     */
    public function beforeDelete()
    {
    	if (parent::beforeDelete()) {
    	    $this->backOutDuesThru();
		    return true;
    	}
    	return false;
    }

    public function getPickList()
    {
        $this->hasOne(DuesAllocPickList::className(), ['id' => 'id']);
    }

    /**
     * @param bool $clear
     * @return bool
     * @throws Exception
     */
    public function backOutDuesThru($clear = false)
    {
        if(($this->months != null) && ($this->member->dues_paid_thru_dt == $this->paid_thru_dt)) {
            $dt = $this->calcPaidThru($this->months, OpDate::OP_SUBTRACT);
            $member = $this->member;
            $member->dues_paid_thru_dt = $dt;
            $member->overage = isset($member->matchingOverage) ? $member->matchingOverage->overage : 0.00;
            $member->save();
            if($clear) {
                $this->months = null;
                $this->paid_thru_dt = null;
            }
            return true;
        }
        return false;
    }

    /**
     * Uses Standing class to estimate the dues owed for a member
     *
     * @param bool $apf_only When true, calculate based on APF only
     * @return number|NULL
     */
    public function estimateOwed($apf_only = false)
    {
        if (!isset($this->alloc_memb_id))
    		return null;
    	$standing = $this->getStanding($apf_only);
    	return $standing->getDuesBalance();
    }

    /**
     * Calculates number of months covered by $allocation_amt property.  Any remainder updates
     * $unalloc_remainder property
     *
     * @param float $overage
     * @return NULL|integer
     * @throws Exception
     */
    public function calcMonths($overage = 0.00)
    {
        $tab = $this->allocation_amt + $overage;
        $result = FeeCalendar::getPeriodsCovered($this->getLobCd(), $this->getRateClass(), $this->getStartDt(), $tab);
        $this->unalloc_remainder = $result['overage'];
        return $result['periods'];
    }

    /**
     * Calculates the new dues paid thru date.
     *
     * @param null $months
     * @param null $op
     * @return string Paid thru date in MySql format
     * @throws Exception
     */
    public function  calcPaidThru($months = null, $op = null)
    {
    	if ($months === null)
    		$months = $this->calcMonths();
    	if ($op === null)
    		$op = OpDate::OP_ADD;
    	$paid_thru = (new OpDate)->setFromMySql($this->getStartDt());
    	$paid_thru->modify($op . $months . ' month');
    	$paid_thru->setToMonthEnd();
    	return $paid_thru->getMySqlDate();
    }

    private function getLobCd()
    {
        if(!(isset($this->lob_cd)))
            $this->lob_cd = isset($this->member->currentStatus) ? $this->member->currentStatus->lob_cd : null;
        return $this->lob_cd;
    }

    private function getRateClass()
    {
        if(!(isset($this->rate_class)))
            $this->rate_class = isset($this->member->currentClass) ? $this->member->currentClass->rate_class : 'R';
        return $this->rate_class;
    }

    private function getStartDt()
    {
        if(!(isset($this->start_dt)))
            $this->start_dt = $this->member->dues_paid_thru_dt;
        return $this->start_dt;
    }

}
