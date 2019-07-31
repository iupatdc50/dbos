<?php


namespace app\models\training;

use yii\base\InvalidCallException;
use yii\base\Model;
use yii\db\ActiveQuery;

class MemberCompliance extends Model
{
    /**
     * This query is more efficient that using a UNION view because it filters by member first
     *
     * @param $member_id
     * @param $catg
     * @return ActiveQuery
     */
    public static function findMemberComplianceByCatg($member_id, $catg)
    {
        $sql = self::credentialSql() . ' UNION ALL ' . self::scheduleSql() . ' ORDER BY display_seq';
        return CurrentMemberCredential::findBySql($sql, ['member_id' => $member_id, 'catg' => $catg]);
    }


    public static function findMemberCompliance($member_id, $show_on = null)
    {
        $and_show_on = '';
        if (isset($show_on)) {
            if(in_array($show_on, ['show_on_id', 'show_on_cert']))
            {
                $and_show_on = " AND {$show_on} = 'T' ";
            } else {
                throw new InvalidCallException("Invalid parameter `{$show_on}``");
            }
        }
        $sql = self::credentialSql(false) . $and_show_on . ' UNION ALL ' . self::scheduleSql(false) . $and_show_on . ' ORDER BY display_seq';
        return CurrentMemberCredential::findBySql($sql, ['member_id' => $member_id]);
    }

    /**
     * MemberRespirators will only exist for credential_id 28 (Respirator Fit test)
     *
     * @param bool $use_catg
     * @return string
     */
    private static function credentialSql($use_catg = true)
    {
        /** @noinspection SqlCaseVsIf */
        $sql = <<<SQL
        
  SELECT 
      MC.`id`, 
      MC.member_id, 
      MC.credential_id, 
      CASE WHEN MR.complete_dt IS NOT NULL 
          THEN CONCAT(Cr.credential, ' (', MR.brand, ':', MR.resp_size, ':', MR.resp_type, ')') 
          ELSE Cr.credential 
      END AS credential, 
      Cr.card_descrip, 
      MC.complete_dt, 
      MC.expire_dt, 
      Cr.catg, 
      Cr.display_seq, 
      Cr.show_on_id,
      Cr.show_on_cert,
      MS.schedule_dt
    FROM MemberCredentials AS MC
      LEFT OUTER JOIN MemberCredentials AS B ON B.member_id = MC.member_id
                                                   AND B.credential_id = MC.credential_id
                                                   AND B.complete_dt > MC.complete_dt
      JOIN Credentials AS Cr ON Cr.`id` = MC.credential_id

      LEFT OUTER JOIN MemberScheduled AS MS ON MS.member_id = MC.member_id
                                                  AND MS.credential_id = MC.credential_id
                                                  AND MS.schedule_dt > MC.complete_dt
      LEFT OUTER JOIN MemberRespirators AS MR ON MC.member_id = MR.member_id 
                                               AND MC.credential_id = MR.credential_id 
                                               AND MC.complete_dt = MR.complete_dt                                            
    WHERE B.complete_dt IS NULL -- (B)igger date not found
      AND MC.member_id = :member_id

SQL;
        if($use_catg)
            $sql .= 'AND Cr.catg = :catg';

        return $sql;
    }

    /**
     * @param bool $use_catg
     * @return string
     */
    private static function scheduleSql($use_catg = true)
    {
        $sql =  <<<SQL
  SELECT 
      MS.`id` * -1 AS `id`,
      MS.member_id, 
      MS.credential_id, 
      Cr.credential, 
      Cr.card_descrip, 
      NULL AS complete_dt, 
      NULL AS expire_dt, 
      Cr.catg, 
      Cr.display_seq, 
      Cr.show_on_id,
      Cr.show_on_cert,
      MS.schedule_dt
    FROM MemberScheduled AS MS
      JOIN Credentials AS Cr ON Cr.`id` = MS.credential_id
    WHERE MS.member_id = :member_id 
      AND NOT EXISTS (
          SELECT 1 FROM MemberCredentials 
            WHERE member_id = MS.member_id AND credential_id = MS.credential_id 
    ) 

SQL;
        if($use_catg)
            $sql .= 'AND Cr.catg = :catg';

        return $sql;
    }


}