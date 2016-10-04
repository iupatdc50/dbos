<?php

namespace app\models\member;

use Yii;

/**
 * This is the model class for table "AllowableClassDescriptions".
 *
 * @property string $class_id
 * @property string $class_descrip
 * @property string $member_class
 * @property string $rate_class
 */
class ComboClassDescription extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ComboClassDescriptions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_class', 'rate_class'], 'required'],
            [['class_id'], 'string', 'max' => 3],
            [['class_descrip'], 'string', 'max' => 101],
            [['member_class'], 'string', 'max' => 1],
            [['rate_class'], 'string', 'max' => 2],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'class_id' => 'Class ID',
            'class_descrip' => 'Class Descrip',
            'member_class' => 'Member Class',
            'rate_class' => 'Rate Class',
        ];
    }
}
