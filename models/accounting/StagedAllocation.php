<?php

namespace app\models\accounting;

use Yii;
use app\models\member\Member;
use app\modules\admin\models\FeeType;

/**
 * This is the model class for the StagedAllocation_ tables, which flattens the contractor
 * receipt allocations by allocated member
 *
 * @property integer $alloc_memb_id
 * @property string $member_id
 * 
 * (Properties for each fee_type selected)
 *
 */
class StagedAllocation extends \yii\db\ActiveRecord
{
	
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
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
    	$fee_columns = FeeType::find()->select('fee_type')->asArray;
    	return [
    			[['alloc_memb_id'], 'integer'],
    			[['member_id', 'fullName', 'reportId'], 'safe'],
    			[$fee_columns, 'number'],
    	];
    }    
    
    /**
     * Builds temporary staging table
     * 
     * @param int $receipt_id
     */
    public static function makeTable($receipt_id)
    {
    	if(!isset(Yii::$app->user->id))
    		throw new yii\base\InvalidConfigException('No user ID exists.  Login required.');
    	
    	$db = Yii::$app->db;
    	$db->createCommand('DROP TABLE IF EXISTS ' . self::tableName())
    	   ->execute();
    	return $db->createCommand("CALL MakeStagedAllocTable (:receipt_id, :session_id)")
    	   		  ->bindValues([
    	   		  		':receipt_id' => $receipt_id,
    	   		  		':session_id' => Yii::$app->user->id,
    	   		  ])
    	   		  ->execute();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
    	return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }
    
}
