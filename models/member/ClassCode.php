<?php

namespace app\models\member;

use Yii;

/**
 * This is the model class for table "MemberClassCodes".
 *
 * @property string $member_class_cd
 * @property string $descrip
 */
class ClassCode extends \yii\db\ActiveRecord
{
    CONST CLASS_HANDLER = 'M';
    CONST CLASS_APPRENTICE = 'A';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberClassCodes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_class_cd'], 'required'],
            [['member_class_cd'], 'string', 'max' => 1],
            [['descrip'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_class_cd' => 'Classification',
            'descrip' => 'Description',
        ];
    }
}
