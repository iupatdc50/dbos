<?php

namespace app\models\training;

use app\models\value\Lob;
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
 * @property string $lob_cd [varchar(4)]
 * @property string $unrestricted [enum('T', 'F')]
 *
 * @property CredCategory $credCategory
 * @property Lob $lob
 * @property MemberCredential[] $memberCredentials
 */
class Credential extends ActiveRecord
{
    const HAZ_LEAD = 25;
    const RESP_FIT = 28;
    const DRUG = 36;

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
            [['credential', 'display_seq', 'catg', 'unrestricted'], 'required'],
            [['display_seq', 'duration'], 'integer'],
            [['show_on_cert', 'show_on_id', 'unrestricted'], 'string'],
            [['credential', 'card_descrip'], 'string', 'max' => 20],
            [['catg'], 'string', 'max' => 2],
            [['catg'], 'exist', 'skipOnError' => true, 'targetClass' => CredCategory::className(), 'targetAttribute' => ['catg' => 'catg']],
            [['lob_cd'], 'string', 'max' => 4],
            [['lob_cd'], 'exist', 'skipOnError' => true, 'targetClass' => Lob::className(), 'targetAttribute' => ['lob_cd' => 'lob_cd']],
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
            'lob_cd' => 'Trade',
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
    public function getLob()
    {
        return $this->hasOne(Lob::className(), ['lob_cd' => 'lob_cd']);
    }

    /**
     * @return ActiveQuery
     */
    public function getMemberCredentials()
    {
        return $this->hasMany(MemberCredential::className(), ['credential_id' => 'id']);
    }

}
