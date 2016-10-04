<?php

namespace app\models\accounting;

use Yii;
use app\modules\admin\models\FeeType;

/**
 * This is the model class for table "ReceiptAllocSumms".
 *
 * @property integer $receipt_id
 * @property string $descrip
 * @property string $amount
 */
class ReceiptAllocSumm extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ReceiptAllocSumms';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receipt_id'], 'integer'],
            [['descrip'], 'required'],
            [['amount'], 'number'],
            [['descrip'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'receipt_id' => 'Receipt ID',
            'descrip' => 'Description',
            'amount' => 'Amount',
        ];
    }
        
    
}
