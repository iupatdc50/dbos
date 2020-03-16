<?php

namespace app\models\member;

use app\models\base\BaseNote;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "MemberNotes".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $note
 * @property integer $created_at
 * @property integer $created_by
 *
 * @property Member $member
 */
class Note extends BaseNote
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberNotes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
            [['member_id', 'note'], 'required'],
            [['member_id'], 'exist', 'targetClass' => '\app\models\member\Member'],
        ];
        return parent::rules();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $this->_labels = [
            'member_id' => 'Member ID',
        ];
        return parent::attributeLabels();
    }

    /**
     * @return ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }

}
