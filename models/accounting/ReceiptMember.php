<?php

namespace app\models\accounting;

use Yii;
use app\models\member\Member;

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
	 * @return \yii\db\ActiveQuery
	 */
	public function getPayingMember()
	{
		return $this->hasOne(Member::className(), ['member_id' => 'member_id'])
					->via('allocatedMembers')
		;
	}

    /**
     * @param $member_id
     * @param null $year
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getFeeTypesSubmitted($member_id, $year = null)
    {
        $date_constraint = is_null($year) ? '' :
            "  JOIN Receipts AS Re ON Re.`id` = M.receipt_id AND LEFT(Re.acct_month, 4) = '{$year}' ";

        $sql = 
        
            "SELECT DISTINCT A.fee_type, FT.seq
                FROM Allocations AS A 
                  JOIN AllocatedMembers AS M ON M.id = A.alloc_memb_id AND M.member_id = :member_id " .
            $date_constraint . 
            "     JOIN FeeTypes AS FT ON FT.fee_type = A.fee_type
                ORDER BY FT.seq
                ";
        
        $rows = Yii::$app->db->createCommand($sql, [':member_id' => $member_id])->queryAll();
        return $rows;
    }

    /**
     * @param array $rows
     * @param null $year
     * @return string
     */
    public static function getFlattenedReceiptsByMemberSql(array $rows, $year = null)
    {
        $cols = '';
        foreach ($rows as $row)
            $cols .= "SUM(CASE WHEN AA.fee_type = '" . $row['fee_type'] . "' THEN AA.amt ELSE NULL END) AS `" . $row['fee_type'] . "`,";

        $date_constraint = is_null($year) ? '' :
            "  WHERE LEFT(Re.acct_month, 4) = '{$year}' ";

        $sql =
          
           "SELECT 
                Re.`id`,
                Re.received_dt,
                Re.payor_type,
                CASE WHEN Re.payor_type = 'M' THEN 'Member' ELSE Re.payor_nm END AS payor, " .
                $cols .
           "    SUM(CASE WHEN AA.fee_type <> 'HR' THEN AA.amt ELSE 0.00 END) AS total
              FROM Receipts AS Re
                JOIN AllocAbbrev AS AA ON AA.receipt_id = Re.`id` AND AA.member_id = :member_id " .
           $date_constraint .
           " GROUP BY Re.`id`, Re.received_dt, Re.payor_type
            ORDER BY Re.received_dt DESC 
            ";

        return $sql;
    }
	
}