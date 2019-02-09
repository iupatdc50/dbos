<?php

namespace app\models\accounting;

use Yii;

class ReceiptMultiMember extends Receipt
{
	protected $_remit_filter = 'employer_remittable';
	/**
	 * Injected employer object
	 * @var \app\models\accounting\ResponsibleEmployer
	 */
	public $responsible;

    /**
     * Builds an array of all fee types submitted by a contractor, including helper dues and unallocated values. Column
     * namesd on the flattened receipts are the fee type code.  Helper dues and unallocated use the receipt column names
     *
     * @param $license_nbr
     * @param null $year    If entered, then select only for the year indicated
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getFeeTypesSubmitted($license_nbr, $year = null)
    {
        $date_constraint = is_null($year) ? '' :
            "  JOIN Receipts AS Re ON Re.`id` = M.receipt_id AND LEFT(Re.acct_month, 4) = '{$year}' ";

        $sql =

            "SELECT DISTINCT A.fee_type, FT.seq
                FROM Allocations AS A 
                  JOIN AllocatedMembers AS M ON M.id = A.alloc_memb_id 
                    JOIN ResponsibleEmployers AS E ON E.receipt_id = M.receipt_id AND E.license_nbr = :license_nbr 
                  JOIN FeeTypes AS FT ON FT.fee_type = A.fee_type
            {$date_constraint} 
                ORDER BY FT.seq
                ";

        $rows = Yii::$app->db->createCommand($sql, [':license_nbr' => $license_nbr])->queryAll();

        $date_constraint = is_null($year) ? '' :
            "  WHERE LEFT(R.acct_month, 4) = '{$year}' ";

        $sql =
            "SELECT 
                 CASE WHEN unalloc_total > 0 THEN 'T' ELSE 'F' END AS has_unallocs,
                 CASE WHEN helpdues_total > 0 THEN 'T' ELSE 'F' END AS has_helpdues
              FROM (
                  SELECT 
                      SUM(R.unallocated_amt) AS unalloc_total, 
                      SUM(R.helper_dues) AS helpdues_total
                    FROM Receipts AS R
                      JOIN ResponsibleEmployers AS E ON E.receipt_id = R.`id` AND E.license_nbr = :license_nbr " .
            $date_constraint .
            "  ) AS S 
            ";

        $nonallocs = Yii::$app->db->createCommand($sql, [':license_nbr' => $license_nbr])->queryAll();
        foreach ($nonallocs AS $nonalloc) {
            if ($nonalloc['has_unallocs'] == 'T')
                $rows[] = ['fee_type' => 'unallocated_amt', 'seq' => 30];
            if ($nonalloc['has_helpdues'] == 'T')
                $rows[] = ['fee_type' => 'helper_dues', 'seq' => 31];
            }

        return $rows;
    }

    /**
     * @param $rows  array   Fee types that will be converted from rows ro columns
     * @param null $year     If entered, then select only for the year indicated
     * @return string
     */
    public static function getFlattenedReceiptsByContractorSql(array $rows, $year = null)
    {
        $cols = '';
        foreach ($rows as $row) {
            $cols .= (strlen($row['fee_type']) == 2)  // Assume this is an actual fee type column
                ? "SUM(CASE WHEN AA.fee_type = '" . $row['fee_type'] . "' THEN AA.amt ELSE NULL END) AS `" . $row['fee_type'] . "`,"
                : "CASE WHEN Re.{$row['fee_type']} = 0 THEN NULL ELSE Re.{$row['fee_type']} END AS {$row['fee_type']}, ";
        }

        $date_constraint = is_null($year) ? '' :
            "  WHERE LEFT(Re.acct_month, 4) = '{$year}' ";

        $sql =

            "SELECT 
                Re.`id`,
                Re.received_dt, " .
            $cols .
            "    Re.received_amt AS total
              FROM Receipts AS Re
                JOIN AllocAbbrev AS AA ON AA.receipt_id = Re.`id`
                JOIN ResponsibleEmployers AS E ON E.receipt_id = Re.`id` AND E.license_nbr = :license_nbr " .
            $date_constraint .
            " GROUP BY Re.`id`, Re.received_dt, Re.payor_type
            ORDER BY Re.received_dt DESC 
            ";

        return $sql;
    }

    public function getCustomAttributes($forPrint = false)
    {
        $attrs = parent::getCustomAttributes($forPrint);
        // no feeTypeTexts on printable receipt
        if (!$forPrint) {
            $attrs[] = [
                'attribute' =>  'feeTypeTexts',
                'format' => 'ntext',
                'label' => 'Fee Types'
            ];
        };
        return $attrs;
    }


}
