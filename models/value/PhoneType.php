<?php

namespace app\models\value;

use Yii;

/**
 * This is the model class for table "PhoneTypes".
 *
 * @property string $phone_type
 * @property string $descrip
 *
 */
class PhoneType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'PhoneTypes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone_type', 'descrip'], 'required'],
            [['phone_type'], 'string', 'max' => 1],
            [['descrip'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'phone_type' => 'Phone Type',
            'descrip' => 'Description',
        ];
    }

}
