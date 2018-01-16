<?php

namespace app\models\accounting;

use app\modules\admin\models\FeeType;
use Yii;
use app\models\member\Member;

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
/** @noinspection PropertiesInspection */

/**
 * This is the model class for the StagedAllocation_ tables, which flattens the contractor
 * receipt allocations by allocated member
 *
 * @property integer $receipt_id
 * @property integer $alloc_memb_id
 * @property string $member_id
 * 
 * (Properties for each fee_type selected)
 * 
 * @property Member $member
 *
 */
class StagedAllocation extends \yii\db\ActiveRecord
{
    //Used to make variable fee_type columns update safe in rules()
    public $fee_types = [];
	//Search place holders
	public $classification;
	public $reportId;
	public $fullName;
	
	public static function primaryKey()
	{
		return ['alloc_memb_id'];
	}
	
    /**
     * Table name is unique by user.  If user is logged in multiple times simultaneously, staged
     * table will be overlaid.
     * 
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'StagedAllocations' . Yii::$app->user->id;
    }

    public function init()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->fee_types = array_diff(self::getTableSchema()->getColumnNames(), ['receipt_id', 'alloc_memb_id', 'member_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
		return [
    			[['alloc_memb_id'], 'integer'],
    			[['member_id', 'classification', 'fullName', 'reportId'], 'safe'],
    			[$this->fee_types, 'number'],
    	];
    }

    /**
     * Builds temporary staging table from exisiting allocations.
     *
     * If the receipt has allocations stored on the database, then SQL code is used to generate
     * fee columns on staging table.  If there are no allocations
     *
     * @param Receipt $receipt
     * @param array $fee_types  Required if no allocations exist
     * @return int
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public static function makeTable(Receipt $receipt, array $fee_types = [])
    {
    	if(!isset(Yii::$app->user->id))
    		throw new \yii\base\InvalidConfigException('No user ID exists.  Login required.');
    	
    	$db = Yii::$app->db;
    	$db->createCommand('DROP TABLE IF EXISTS ' . self::tableName())
    	   ->execute();

    	$hasAllocs = ($receipt->allocatedCount > 0);

    	if($hasAllocs) {
            $result = $db->createCommand("CALL MakeStagedAllocTable (:receipt_id, :session_id)", [
                ':receipt_id' => $receipt->id,
                ':session_id' => Yii::$app->user->id
            ])->execute();
        } else {
    	    $fee_columns = '';
    	    foreach ($fee_types as $fee_type)
    	        $fee_columns .= strtoupper(", {$fee_type} DECIMAL(9,2) ");
    	    $sql = "CREATE TABLE " . self::tableName() . " (receipt_id INT, alloc_memb_id INT, member_id VARCHAR(11){$fee_columns});";

    	    $result = $db->createCommand($sql)->execute();
        }

        return $result;

    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
    	return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }

    public function getFeeLabels()
    {
        $labels = [];
        foreach ($this->fee_types as $col_nm) {
            /** @var FeeType $ref */
            $ref = FeeType::findOne($col_nm);
            $labels[$col_nm] = $ref->short_descrip;
        }
        return $labels;
    }

    public function getFeeTypeDescrips()
    {
        if (!isset($this->feeTypeDescrips))
            $this->feeTypeDescrips = FeeType::find()->all();
        return $this->feeTypeDescrips;
    }

}
