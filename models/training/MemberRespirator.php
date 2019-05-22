<?php

namespace app\models\training;

use app\models\value\RespiratorBrand;
use app\models\value\Size;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "MemberRespirators".
 *
 * @property string $member_id
 * @property integer $credential_id
 * @property string $complete_dt
 * @property string $brand
 * @property string $resp_size
 * @property string $resp_type
 *
 * @property MemberCredRespFit $memberCredRespFit
 * @property RespiratorBrand $respiratorBrand
 * @property Size $respSize
 */
class MemberRespirator extends ActiveRecord
{
    const HALF_FACE = 'HALF FACE';
    const FULL_FACE = 'FULL FACE';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberRespirators';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['brand', 'resp_size', 'resp_type'], 'required'],
            [['credential_id'], 'in', 'range' => [Credential::RESP_FIT]],
            [['credential_id'], 'default', 'value' => Credential::RESP_FIT],
            [['resp_type'], 'in', 'range' => [self::HALF_FACE, self::FULL_FACE]],
            [['member_id'], 'string', 'max' => 11],
            [['brand'], 'string', 'max' => 15],
            [['resp_size'], 'string', 'max' => 3],
            [['member_id', 'credential_id', 'complete_dt'], 'exist',
                'skipOnError' => true,
                'targetClass' => MemberCredential::className(),
                'targetAttribute' => ['member_id' => 'member_id', 'credential_id' => 'credential_id', 'complete_dt' => 'complete_dt'],
            ],
            [['brand'], 'exist', 'skipOnError' => true, 'targetClass' => RespiratorBrand::className(), 'targetAttribute' => ['brand' => 'brand']],
            [['resp_size'], 'exist', 'skipOnError' => true, 'targetClass' => Size::className(), 'targetAttribute' => ['resp_size' => 'size_cd']],
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
            'brand' => 'Brand',
            'resp_size' => 'Size',
            'resp_type' => 'Type',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getMemberCredRespFit()
    {
        return $this->hasOne(MemberCredRespFit::className(), ['member_id' => 'member_id', 'credential_id' => 'credential_id', 'complete_dt' => 'complete_dt']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRespiratorBrand()
    {
        return $this->hasOne(RespiratorBrand::className(), ['brand' => 'brand']);
    }

    public function getBrandOptions()
    {
        return ArrayHelper::map(RespiratorBrand::find()->orderBy('brand')->all(), 'brand', 'brand');
    }

    /**
     * @return ActiveQuery
     */
    public function getRespSize()
    {
        return $this->hasOne(Size::className(), ['size_cd' => 'resp_size']);
    }

    public function getSizeOptions()
    {
        return ArrayHelper::map(Size::find()->where('seq < 5')->orderBy('seq')->all(), 'size_cd', 'size_cd');
    }

    public function getTypeOptions()
    {
        return [self::HALF_FACE => self::HALF_FACE, self::FULL_FACE => self::FULL_FACE];
    }


}
