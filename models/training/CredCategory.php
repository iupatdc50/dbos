<?php

namespace app\models\training;

use Yii;

/**
 * This is the model class for table "CredCategories".
 *
 * @property string $catg
 * @property string $descrip
 *
 * @property Credential[] $credentials
 */
class CredCategory extends \yii\db\ActiveRecord
{
    CONST CATG_RECURRING = 'RC';
    CONST CATG_NONRECUR = 'NR';
    CONST CATG_MEDTESTS = 'MT';
    CONST CATG_CORE = 'PD';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'CredCategories';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['catg', 'descrip'], 'required'],
            [['catg'], 'string', 'max' => 2],
            [['descrip'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'catg' => 'Catg',
            'descrip' => 'Descrip',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCredentials()
    {
        return $this->hasMany(Credential::className(), ['catg' => 'catg']);
    }
}
