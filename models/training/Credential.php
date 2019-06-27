<?php

namespace app\models\training;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "Credentials".
 *
 * @property integer $id
 * @property string $credential
 * @property integer $display_seq
 * @property string $card_descrip
 * @property string $catg
 * @property integer $duration
 * @property string $show_on_cert
 * @property string $show_on_id
 *
 * @property CredCategory $credCategory
 * @property MemberCredential[] $memberCredentials
 */
class Credential extends ActiveRecord
{
    const HAZ_LEAD = 25;
    const RESP_FIT = 28;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Credentials';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['credential', 'display_seq', 'catg'], 'required'],
            [['display_seq', 'duration'], 'integer'],
            [['show_on_cert', 'show_on_id'], 'string'],
            [['credential', 'card_descrip'], 'string', 'max' => 20],
            [['catg'], 'string', 'max' => 2],
            [['catg'], 'exist', 'skipOnError' => true, 'targetClass' => CredCategory::className(), 'targetAttribute' => ['catg' => 'catg']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'credential' => 'Credential',
            'display_seq' => 'Display Seq',
            'card_descrip' => 'Card Descrip',
            'catg' => 'Catg',
            'duration' => 'Duration',
            'show_on_cert' => 'Show On Cert',
            'show_on_id' => 'Show On ID',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCredCategory()
    {
        return $this->hasOne(CredCategory::className(), ['catg' => 'catg']);
    }

    /**
     * @return ActiveQuery
     */
    public function getMemberCredentials()
    {
        return $this->hasMany(MemberCredential::className(), ['credential_id' => 'id']);
    }

}
