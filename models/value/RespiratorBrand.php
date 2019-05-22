<?php

namespace app\models\value;

use app\models\training\MemberRespirator;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "RespiratorBrands".
 *
 * @property string $brand
 *
 * @property MemberRespirator[] $memberRespirator
 */
class RespiratorBrand extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'RespiratorBrands';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['brand'], 'required'],
            [['brand'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'brand' => 'Brand',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getMemberRespirator()
    {
        return $this->hasMany(MemberRespirator::className(), ['brand' => 'brand']);
    }
}
