<?php

namespace app\models\member;

use yii\base\Model;

class PacExport extends Model
{
    CONST FROM_CLAUSE = "
			    FROM Members AS Me
				  JOIN MemberContactInfo AS MCI ON MCI.member_id = Me.member_id
			      JOIN (
			        SELECT 
			            Me.member_id, 
			            SUM(Al.allocation_amt * SI.local_pac_rate) AS contribution
			          FROM Members AS Me
			            JOIN AllocatedMembers AS AM ON AM.member_id = Me.member_id
			              JOIN Receipts AS Re ON Re.`id` = AM.receipt_id
			              JOIN Allocations AS Al ON Al.alloc_memb_id = AM.`id`
			                                   AND Al.fee_type = 'HR'
			            ,SystemInfo AS SI
			          WHERE 1 = 1
			            AND Re.received_dt BETWEEN :begin_dt AND :end_dt
			          GROUP BY Me.member_id
			      
				    ) AS H ON H.member_id = Me.member_id	
			    WHERE Me.local_pac = 'T'
				  AND MCI.lob_cd = :lob_cd
    		
    ";
	public static function sumContribution($criteria = []) {
		$sql = "SELECT SUM(H.contribution) " . self::FROM_CLAUSE . ";";
		$db = \Yii::$app->db;
		$cmd = $db->createCommand($sql)
		->bindValues([
				':begin_dt' => $criteria['begin_dt'],
				':end_dt' => $criteria['end_dt'],
				':lob_cd' => $criteria['lob_cd'],
		]);
		return $cmd->queryScalar();
		
	}
	
	public static function findByCriteria($criteria = []) {
		$sql = "
			  SELECT 
			      'A' AS field_01,
			      'NC20128' AS field_02,
			      Me.report_id AS field_03,
			      Me.ncfs_id AS field_04,
			      'IND' AS field_05,
			      Me.first_nm  AS field_06,
			      MCI.mi AS field_07,
			      Me.last_nm AS field_08,
			      Me.suffix AS field_09,
			      MCI.address_ln1 AS field_10,
			      MCI.address_ln2 AS field_11,
			      MCI.city AS field_12,
			      MCI.st AS field_13,
			      MCI.zip_cd AS field_14,
			      CASE MCI.lob_cd
			        WHEN '1791' THEN 'Painter'
			        ELSE 'NA'
			        END AS field_15,
			      MCI.employer AS field_16,
			      NULL AS field_17,
			      NULL AS field_18,
			      NULL AS field_19,
			      NULL AS field_20,
			      NULL AS field_21,
			      NULL AS field_22,
			      NULL AS field_23,
			      NULL AS field_24,
			      NULL AS field_25,
			      NULL AS field_26,
			      NULL AS field_27,
			      NULL AS field_28,
			      NULL AS field_29,
			      NULL AS field_30,
			      DATE_FORMAT(CURDATE(), '%Y%m%d') AS field_31,
			      NULL AS field_32,
			      H.contribution AS field_33,
			      'N' AS field_34,
			      NULL AS field_35,
			      NULL AS field_36
				". self::FROM_CLAUSE . "
			;		
		";
		$db = \Yii::$app->db;
		$cmd = $db->createCommand($sql)
				  ->bindValues([
						':begin_dt' => $criteria['begin_dt'],
						':end_dt' => $criteria['end_dt'],
				  		':lob_cd' => $criteria['lob_cd'],
				  ]);
		return $cmd->queryAll();
		
	}
	
	
}