<?php

namespace app\models\accounting;

use Yii;

/**
 * This is the model class for table "AdminFees".
 *
 * @property string $fee_type
 * @property string $effective_dt
 * @property string $end_dt
 * @property string $fee
 *
 * @property FeeTypes $feeType
 */
class AdminFee extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AdminFees';
    }
    
	/**
	 * Return any preset admin fee that matches the fee type
	 * 
	 * @param string $fee_type
	 * @param string $date  MySQL format 
	 */
    public static function getFee($fee_type, $date)
    {
    	$sql = "SELECT fee FROM " . self::tableName() .
    	"  WHERE fee_type = :fee_type
    				AND effective_dt <= :date
    				AND (end_dt IS NULL OR end_dt >= :date)
    			;";
    	$db = yii::$app->db;
    	$cmd = $db->createCommand($sql)
    	->bindValues([
    			':fee_type' => $fee_type,
    			':date' => $date,
    	]);
    	return $cmd->queryScalar();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fee_type', 'effective_dt', 'fee'], 'required'],
            [['effective_dt', 'end_dt'], 'safe'],
            [['fee'], 'number'],
            [['fee_type'], 'string', 'max' => 2],
            [['fee_type'], 'exist', 'skipOnError' => true, 'targetClass' => FeeTypes::className(), 'targetAttribute' => ['fee_type' => 'fee_type']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fee_type' => 'Fee Type',
            'effective_dt' => 'Effective Dt',
            'end_dt' => 'End Dt',
            'fee' => 'Fee',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeeType()
    {
        return $this->hasOne(FeeTypes::className(), ['fee_type' => 'fee_type']);
    }
}
