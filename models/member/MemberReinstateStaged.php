<?php

namespace app\models\member;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "MemberReinstateStaged".
 *
 * @property string $member_id
 * @property string $reinstate_type
 * @property string $dues_owed_amt [decimal(9,2)]
 *
 * @property Member $member
 */
class MemberReinstateStaged extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'MemberReinstateStaged';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'reinstate_type', 'dues_owed_amt'], 'required'],
            [['reinstate_type'], 'string'],
            ['reinstate_type',  'in', 'range' => ReinstateForm::getAllowedTypes()],
            [['member_id'], 'string', 'max' => 11],
            [['member_id'], 'unique'],
            [['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => Member::className(), 'targetAttribute' => ['member_id' => 'member_id']],
            ['dues_owed_amt', 'number'],
            ['dues_owed_amt', 'default', 'value' => 0.00],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'reinstate_type' => 'Reinstate Type',
            'dues_owed_amt' => 'Dues Owed',
        ];
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $result = true;

            if ($this->reinstate_type == ReinstateForm::TYPE_BACKDUES)
                // Check for forgiven back dues
                if ($this->dues_owed_amt == 0.00) {
                    $this->member->dues_paid_thru_dt = $this->member->getDuesStartDt(true)->getMySqlDate();
                    $result = $this->member->save();
 //               if ($this->) {

                }
            return $result;
        }
        return false;
    }

    /**
     * Gets query for [[Member]].
     *
     * @return ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }

}
