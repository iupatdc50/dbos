<?php

namespace app\models\accounting;

use yii\data\ActiveDataProvider;
use app\models\member\Member;
use app\models\member\Standing;
use app\models\contractor\Contractor;
use app\modules\admin\models\FeeType;

class StagedBill extends \yii\db\ActiveRecord
{
	/**
	 * @var Standing 	May be injected, if required
	 */
	private $_standing;
	/**
	 * @var DuesRateFinder 	May be injected, if required
	 */
	private $_rateFinder;
	
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'StagedBills';
	}
	
	public function attributes()
	{
		return array_merge(parent::attributes(), ['PC', 'JT', 'IU', 'LM', 'PA']);
	}
	
	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getMember()
	{
		return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
	}

    /**
     * @return Standing
     */
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
	
	public function getRateFinder()
	{
		if(!(isset($this->_rateFinder))) {
			$member = $this->member;
			$lob_cd = $member->currentStatus->lob_cd;
			$rate_class = $member->currentClass->rate_class;
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->_rateFinder = new DuesRateFinder($lob_cd, $rate_class);
		}
		return $this->_rateFinder;
	}
	
	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getEmployer()
	{
		return $this->hasOne(Contractor::className(), ['license_nbr' => 'dues_payor']);
	}
	
	/**
	 * Determines dues amount employer can collect for this member
	 * 
	 * @return number
	 */
	public function getDU()
	{
		if ($this->employer->deducts_dues == 'T')
			return $this->standing->getDuesBalance($this->rateFinder);
		return null;
	}
	
	/**
	 * Determines APF amount employer can collect for this member
	 * 
	 * @return number
	 */
	public function getIN()
	{
		if ($this->employer->deducts_dues == 'T') {
		    /* @var $assessment Assessment */
		    $assessment = $this->standing->getOutstandingAssessment(FeeType::TYPE_INIT);
		    if (isset($assessment) && ($assessment->balance <> 0.00))
		        return $assessment->balance;
        }
		return null;
	}
	
	/**
	 * Returns data provider for bill line item with pre-filled formulae fr spreadsheet columns
	 *   
	 * @param string $license_nbr
	 * @param string $lob_cd
	 * @return \yii\data\ActiveDataProvider
	 */
	public function getPreFill($license_nbr, $lob_cd)
	{
		// @row_number counter starts at 1 to hold a place for the header, i.e., first data row is 2 on the spreadsheet 
		$sql = <<<SQL

			SELECT 
                dues_payor, member_id,
				classification, last_nm, first_nm, middle_inits, report_id, member_status,
			    CASE WHEN pc_operand IS NULL THEN pc_factor ELSE CONCAT('=ROUND(', pc_operand, seq, '*', pc_factor, ',2)') END AS PC,
			    CASE WHEN jt_operand IS NULL THEN jt_factor ELSE CONCAT('=ROUND(', jt_operand, seq, '*', jt_factor, ',2)') END AS JT,
			    CASE WHEN lm_operand IS NULL THEN lm_factor ELSE CONCAT('=ROUND(', lm_operand, seq, '*', lm_factor, ',2)') END AS LM,
				CASE WHEN iu_operand IS NULL THEN iu_factor ELSE CONCAT('=ROUND(', iu_operand, seq, '*', iu_factor, ',2)') END AS IU,
				CASE WHEN pa_operand IS NULL THEN pa_factor ELSE CONCAT('=ROUND(', pa_operand, seq, '*', pa_factor, ',2)') END AS PA
			  FROM (				
				SELECT 
					(@row_number:=@row_number + 1) AS seq,
					classification, last_nm, first_nm, middle_inits, report_id, member_status,
                    dues_payor, member_id,
					pc_factor, pc_operand,
				    jt_factor, jt_operand,
				    lm_factor, lm_operand,
				    iu_factor, iu_operand,
                    pa_factor, pa_operand
				  FROM (
				    SELECT 
				        classification, last_nm, first_nm, middle_inits, report_id, member_status
                       ,dues_payor
                       ,member_id
	                   ,lob_cd
                       ,pc_factor, pc_operand
                       ,jt_factor, jt_operand
                       ,lm_factor, lm_operand
                       ,iu_factor, iu_operand
                       ,pa_factor, pa_operand
				      FROM StagedBills
				      WHERE dues_payor = :license_nbr
				        AND lob_cd = :lob_cd
				    ORDER BY classification, last_nm, first_nm, middle_inits
				  ) AS b, (SELECT @row_number:=1) AS t
				  		
				) AS f
				
SQL;
		
		$query = StagedBill::findBySql($sql, [':license_nbr' => $license_nbr, ':lob_cd' => $lob_cd]);
		
		$dataProvider = new ActiveDataProvider([
				'query' => $query,
				'pagination' => ['pageSize' => 2000],
		]);
		
		return $dataProvider;
	}

	public function getBillableCount($license_nbr, $lob_cd)
    {
        return StagedBill::find()->where(['dues_payor' => $license_nbr, 'lob_cd' => $lob_cd])->count();
    }
	
}

