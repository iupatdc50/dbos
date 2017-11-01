<?php

namespace app\models\member;

use Yii;

/**
 * This is the model class for table "Classifications".
 *
 * @property string $member_id
 * @property string $classification
 */
class Classification extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Classifications';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id'], 'required'],
            [['member_id'], 'string', 'max' => 11],
            [['classification'], 'string', 'max' => 4],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'classification' => 'Classification',
        ];
    }
}
