<?php

namespace app\models\accounting;

use Yii;

/**
 * This is the model class for table "CCOtherLocals".
 *
 * @property integer $alloc_memb_id
 * @property integer $other_local
 *
 * @property AllocatedMembers $allocMemb
 */
class CcOtherLocal extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'CCOtherLocals';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['alloc_memb_id', 'other_local'], 'required'],
            [['alloc_memb_id', 'other_local'], 'integer'],
            [['alloc_memb_id'], 'exist', 'skipOnError' => true, 'targetClass' => AllocatedMember::className(), 'targetAttribute' => ['alloc_memb_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'alloc_memb_id' => 'Alloc Memb ID',
            'other_local' => 'Other Local',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAllocMemb()
    {
        return $this->hasOne(AllocatedMember::className(), ['id' => 'alloc_memb_id']);
    }
}
