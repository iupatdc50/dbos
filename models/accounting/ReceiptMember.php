<?php

namespace app\models\accounting;

use app\modules\admin\models\FeeType;
use Stripe\Charge;
use Stripe\Customer;
use Yii;
use app\models\member\Member;
use yii\db\ActiveQuery;
use yii\db\Exception;

/**
 * Class ReceiptMember
 * @package app\models\accounting
 *
 * @property Transaction $transaction
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
        return $this->hasOne(Transaction::className(), ['receipt_id' => 'id']);
    }

    /**
     * This override updates any outstanding in progress on-line transaction
     *
     * @param $id
     * @throws Exception
     */
    public function cleanup($id)
    {
        parent::cleanup($id);
        $this->closeInProgressTrans();
    }

    /**
     * Closes outstanding in progress credit card transaction
     */
    public function closeInProgressTrans()
    {
        $transaction = $this->transaction;
        if ($transaction->dbos_status == Transaction::DBOS_INPROGRESS) {
            $transaction->dbos_status = Transaction::DBOS_COMPLETED;
            $transaction->save();
        }
    }

    /**
     * Builds a DBOS receipt from a Stripe-posted Charge and leaves it in
     * an unposted state
     *
     * $payment_data = [
     *      'member_id' => <string>,
     *      'lob_cd' => <string>,
     *      'currency' => 'usd',
     *      'charge' => <decimal, 2>,
     *      'email' => <string>,
     * ];
     *
     * @param array $payment_data
     * @param Customer $customer
     * @param Charge $charge
     * @return bool|int     Receipt ID if successful, false if not
     */
    public static function makeUnposted(array $payment_data, Customer $customer, Charge $charge)
    {
        $member = Member::findOne($payment_data['member_id']);
        /** @noinspection PhpUndefinedFieldInspection */

        $receipt = new Receipt([
            'payor_nm' => $customer->name,
            'payment_method' => self::METHOD_CREDIT,
            'payor_type' => self::PAYOR_MEMBER,
            'received_dt' => date("Y-m-d", $charge->created),
            'received_amt' => $payment_data['charge'],
            'created_at' => $charge->created,
            'remarks' => 'Online dues payment',
            'tracking_nbr' => $charge->metadata->tracking,
            'lob_cd' => $member->currentStatus->lob_cd,
            'acct_month' => date("Ym", $charge->created),
            'updated_at' => $charge->created,
        ]);

        $transaction = Yii::$app->db->beginTransaction();
        if ($receipt->save()) {
            $alloc_memb = new AllocatedMember([
                'receipt_id' => $receipt->id,
                'member_id' => $member->member_id,
            ]);

            $allocs_ok = true;
            if ($alloc_memb->save()) {
                $balance = $receipt->received_amt;
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
                    if ($alloc->fee_type == FeeType::TYPE_CC && $payment_data['other_local'] > 0)
                        $alloc_memb->addOtherLocal(new CcOtherLocal(['other_local' => $payment_data['other_local']]));
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
                        $transaction->commit();
                        return $receipt->id;
                    } catch (Exception $e) {
                        Yii::error('*** RM010: Unable to stage receipt. Error(s) ' . print_r ($e->errorInfo, true));
                        Yii::$app->session->addFlash('error', 'Problem with post. Please contact support.  Error: `RM010`');
                    }
                }
            }
        } else {
            Yii::error('*** RM020: Unable to stage receipt. Error(s) ' . print_r ($receipt->errors, true));
            Yii::$app->session->addFlash('error', 'Problem with post. Please contact support.  Error: `RM020`');
        }
        $transaction->rollBack();
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
            "  JOIN Receipts AS Re ON Re.`id` = M.receipt_id AND LEFT(Re.acct_month, 4) = '{$year}' ";

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
            "  WHERE LEFT(Re.acct_month, 4) = '{$year}' ";

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
	
}