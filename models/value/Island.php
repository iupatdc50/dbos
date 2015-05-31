<?php

namespace app\models\value;

use Yii;

/**
 * This is the model class for table "Islands".
 *
 * @property string $island
 * @property string $island_cd
 *
 * @property ZipCodes[] $zipCodes
 */
class Island extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Islands';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['island', 'island_cd'], 'required'],
            [['island'], 'string', 'max' => 15],
            [['island_cd'], 'string', 'max' => 2]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'island' => 'Island',
            'island_cd' => 'Island Cd',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZipCodes()
    {
        return $this->hasMany(ZipCode::className(), ['island' => 'island']);
    }
}
