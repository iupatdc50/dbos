<?php

namespace app\models\accounting;

use Yii;
use app\models\member\Member;
use app\models\accounting\BaseAllocation;

/**
 * This is the model class for the StagedAllocation_ tables, which flattens the contractor
 * receipt allocations by allocated member
 *
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
	//Used to make variable columns update safe in rules()
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
    
    /**
    public function __construct($fee_types = [], $config = [])
    {
		if (empty($fee_types))
			throw new \BadMethodCallException('Missing parameter fee_types');
		$this->_fee_types = $fee_types;
		parent::__construct($config);
    }
    */
    
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
     * Builds temporary staging table
     * 
     * @param int $receipt_id
     */
    public static function makeTable($receipt_id)
    {
    	if(!isset(Yii::$app->user->id))
    		throw new \yii\base\InvalidConfigException('No user ID exists.  Login required.');
    	
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
    
    public function beforeSave($insert)
    {
    	if(parent::beforeSave($insert)) {
    		if (!$insert) {
    			
    		}
    		return true;
    	}
    	return false;
    }
    
    /*
    public function beforeDelete()
    {
    	$allocs = BaseAllocation::findAll(['alloc_memb_id' => $this->alloc_memb_id]);
    	foreach ($allocs as $alloc)
    		$alloc->delete();
    	return parent::beforeDelete();
    }
    */
}
