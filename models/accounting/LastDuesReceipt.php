<?php

namespace app\models\accounting;

use Yii;

/**
 * This is the model class for table "LastDuesReceipts".
 *
 * @property integer $receipt_id
 * @property string $member_id
 */
class LastDuesReceipt extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'LastDuesReceipts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receipt_id', 'member_id'], 'required'],
            [['receipt_id'], 'integer'],
            [['member_id'], 'string', 'max' => 11],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'receipt_id' => 'Receipt ID',
            'member_id' => 'Member ID',
        ];
    }
}
