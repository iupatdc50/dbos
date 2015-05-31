<?php

namespace app\models\member;

use Yii;

/**
 * This is the model class for table "MemberStatusCodes".
 *
 * @property string $member_status_cd
 * @property string $descrip
 */
class StatusCode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberStatusCodes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_status_cd', 'descrip'], 'required'],
            [['member_status_cd'], 'string', 'max' => 1],
            [['descrip'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_status_cd' => 'Member Status Cd',
            'descrip' => 'Descrip',
        ];
    }
}
