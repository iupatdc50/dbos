<?php

namespace app\models\training;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "DrugTestResults".
 *
 * @property string $member_id
 * @property integer $credential_id
 * @property string $complete_dt
 * @property string $test_result
 *
 * @property MemberCredDrug $memberCredDrug
 */
class DrugTestResult extends ActiveRecord
{
    const POSITIVE = 'POSITIVE';
    const NEGATIVE = 'NEGATIVE';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'DrugTestResults';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['credential_id'], 'required'],
            [['credential_id'], 'in', 'range' => [Credential::DRUG]],
            [['credential_id'], 'default', 'value' => Credential::DRUG],
            [['test_result'], 'in', 'range' => [self::POSITIVE, self::NEGATIVE]],
            [['member_id'], 'string', 'max' => 11],
            [['member_id', 'credential_id', 'complete_dt'], 'exist',
                'skipOnError' => true,
                'targetClass' => MemberCredential::className(),
                'targetAttribute' => ['member_id' => 'member_id', 'credential_id' => 'credential_id', 'complete_dt' => 'complete_dt']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'credential_id' => 'Credential ID',
            'complete_dt' => 'Complete Dt',
            'test_result' => 'Test Result',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getMemberCredDrug()
    {
        return $this->hasOne(MemberCredDrug::className(), ['member_id' => 'member_id', 'credential_id' => 'credential_id', 'complete_dt' => 'complete_dt']);
    }

    public function getResultOptions()
    {
        return [self::NEGATIVE => self::NEGATIVE, self::POSITIVE => self::POSITIVE];
    }
}
