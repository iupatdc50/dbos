<?php

namespace app\models\accounting;

use app\modules\admin\models\FeeType;
use Stripe\Charge;
use Yii;
use app\models\member\Member;
use yii\db\ActiveQuery;
use yii\db\Exception;

/**
 * Class ReceiptMember
 * @package app\models\accounting
 *
 * @property StripeTransaction $transaction
 * @property Member $payingMember
 */
class ReceiptMember extends Receipt
{
	public $other_local;
	protected $_remit_filter = 'member_remittable';

    public static function payorType()
    {
        return self::PAYOR_MEMBER;
    }

    public static function find()
    {
        return new ReceiptQuery(get_called_class(), ['type' => self::payorType(), 'tableName' => self::tableName()]);
    }

    /**
	 * @inheritdoc
	 */
	public function rules()
	{
		$this->_validationRules = [
				[['other_local'], 'safe'],
		];
		return parent::rules();
	}

	/**
	 * Assume that member receipt applies to only one member
	 * 
	 * @return ActiveQuery
	 */
	public function getPayingMember()
	{
		return $this->hasOne(Member::className(), ['member_id' => 'member_id'])
					->via('allocatedMembers')
		;
	}

    /**
     * @return ActiveQuery
     */
	public function getTransaction()
    {
        return $this->hasOne(StripeTransaction::className(), ['receipt_id' => 'id']);
    }

    /**
     * This override updates any outstanding in progress on-line transaction
     *
     * @throws Exception
     */
    public function cleanup()
    {
        parent::cleanup();
        // Determine if the completed process is part of a reinstatement cycle
        $this->payingMember->removeReinstateStaged();
    }

    /**
     * Builds a DBOS receipt from a Stripe-posted Charge and leaves it in
     * an unposted state
     *
     * @param Member $member
     * @param Charge $charge
     * @param null $other_local Used to pass other local for CCD
     * @return bool|int     Receipt ID if successful, false if not
     */
    public function makeUnposted(Member $member, Charge $charge, $other_local = null)
    {
        $this->payor_nm = $charge->metadata['cardholder_nm'];
        $this->payment_method = self::METHOD_CREDIT;
        $this->payor_type = self::PAYOR_MEMBER;
        $this->received_amt = floatval($charge->amount / 100);
        $this->remarks = 'Online dues/fees payment';
        $this->tracking_nbr = $charge->metadata['tracking'];
        $this->lob_cd = $charge->metadata['trade'];

        /*
         * Placeholder to satisfy Receipt SCENARIO_CREATE rule
         * $this->fee_types unnecessary for Stripe-posted receipts
         */
        $this->fee_types = ['**'];

        if ($this->save()) {
            $alloc_memb = new AllocatedMember([
                'receipt_id' => $this->id,
                'member_id' => $member->member_id,
            ]);

            $allocs_ok = true;
            if ($alloc_memb->save()) {
                $balance = $this->received_amt;
                foreach ($member->feeBalances as $fee) {
                    $alloc = new AssessmentAllocation([
                        'alloc_memb_id' => $alloc_memb->id,
                        'fee_type' => $fee->fee_type,
                        'allocation_amt' => ($fee->balance_amt) > $balance ? $balance : $fee->balance_amt,
                    ]);
                    if (!$alloc->save()) {
                        $allocs_ok = false;
                        break;
                    }
                    $balance -= $alloc->allocation_amt;
                    if ($alloc->fee_type == FeeType::TYPE_CC && $other_local > 0)
                        $alloc_memb->addOtherLocal(new CcOtherLocal(['other_local' => $other_local]));
                }

                if ($allocs_ok) {
                    if ($balance > 0.00) {
                        $alloc = new DuesAllocation([
                            'alloc_memb_id' => $alloc_memb->id,
                            'fee_type' => 'DU',
                            'allocation_amt' => $balance,
                        ]);
                        if (!$alloc->save())
                            $allocs_ok = false;

                    }
                }

                if ($allocs_ok) {
                    // Put the receipt in unposted mode
                    try {
                        $this->makeUndo($this->id);
                        return $this->id;
                    } catch (Exception $e) {
                        $this->exceptionHandler('RM010', 'Unable to stage receipt undo', $e->errorInfo);
                    }
                }
            }
        } else {
            $this->exceptionHandler('RM020', 'Unable to stage receipt', $this->errors);
        }
        return false;
    }

    /**
     * Builds and posts a DBOS receipt from a Stripe-posted subscription invoice paid.
     *
     * Assumes that there is only 1 month of dues at a time for each invoice
     *
     * @param Member $member
     * @param Charge $charge Expects Customer expanded
     * @param $tracking_nbr
     * @return int receipt_id
     * @throws \Exception
     */
    public function makePosted(Member $member, Charge $charge, $tracking_nbr)
    {
        $this->payor_nm = $charge->customer->name;
        $this->payment_method = self::METHOD_CREDIT;
        $this->payor_type = self::PAYOR_MEMBER;
        $this->received_amt = floatval($charge->amount / 100);
        $this->remarks = 'Subscription dues payment';
        $this->tracking_nbr = $tracking_nbr;
        $this->lob_cd = $member->currentStatus->lob_cd;

        $this->fee_types = [FeeType::TYPE_DUES];

        if ($this->save()) {
            $alloc_memb = new AllocatedMember([
                'receipt_id' => $this->id,
                'member_id' => $member->member_id,
            ]);

            if ($alloc_memb->save()) {
                $alloc = new DuesAllocation([
                    'alloc_memb_id' => $alloc_memb->id,
                    'fee_type' => FeeType::TYPE_DUES,
                    'allocation_amt' => $this->received_amt,
                    'months' => 1,
                ]);
                $alloc->paid_thru_dt = $alloc->calcPaidThru(1);
                if ($alloc->save()) {
                    $member->dues_paid_thru_dt = $alloc->paid_thru_dt;
                    if ($member->save())
                        return $this->id;
                }
            }
        }
        $this->exceptionHandler('RM021', 'Unable to post receipt', $this->errors);
        return false;
    }

    /**
     * @param $member_id
     * @param null $year
     * @return array
     * @throws Exception
     */
    public static function getFeeTypesSubmitted($member_id, $year = null)
    {
        $date_constraint = is_null($year) ? '' :
            "  JOIN Receipts AS Re ON Re.`id` = M.receipt_id AND LEFT(Re.acct_month, 4) = '$year' ";

        /** @noinspection SqlResolve */
        $sql =
        
            "SELECT DISTINCT A.fee_type, FT.seq
                FROM Allocations AS A 
                  JOIN AllocatedMembers AS M ON M.id = A.alloc_memb_id AND M.member_id = :member_id " .
            $date_constraint . 
            "     JOIN FeeTypes AS FT ON FT.fee_type = A.fee_type
                ORDER BY FT.seq
                ";
        
        return Yii::$app->db->createCommand($sql, [':member_id' => $member_id])->queryAll();
    }

    /**
     * @param array $rows
     * @param bool $total_only
     * @param null $year
     * @return string
     */
    public static function getFlattenedReceiptsByMemberSql(array $rows, $total_only = false, $year = null)
    {
        $group_cols = ($total_only) ? '' :
             "  Re.`id`,
                Re.received_dt,
                Re.payor_type,
                CASE WHEN Re.payor_type = 'M' THEN 'Member' ELSE Re.payor_nm END AS payor, ";

        $cols = '';
        foreach ($rows as $row)
            $cols .= "SUM(CASE WHEN AA.fee_type = '" . $row['fee_type'] . "' THEN AA.amt ELSE NULL END) AS `" . $row['fee_type'] . "`,";

        $date_constraint = is_null($year) ? '' :
            "  WHERE LEFT(Re.acct_month, 4) = '$year' ";

        $group_by = ($total_only) ? '' :
            " GROUP BY Re.`id`, Re.received_dt, Re.payor_type 
              ORDER BY Re.received_dt DESC";

        return
          
           "SELECT  " .
                $group_cols .
                $cols .
           "    SUM(CASE WHEN AA.fee_type <> 'HR' THEN AA.amt ELSE 0.00 END) AS total
              FROM Receipts AS Re
                JOIN AllocAbbrev AS AA ON AA.receipt_id = Re.`id` AND AA.member_id = :member_id " .
           $date_constraint .
           $group_by
        ;

    }

    protected function setReceivedDefault()
    {
        parent::setReceivedDefault();
        if (!isset($this->period)) {
            $date = $this->getRecdDtObj();
            $this->period = $date->getYearMonth();
        }

    }

}