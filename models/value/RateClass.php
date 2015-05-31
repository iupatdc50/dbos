<?php

namespace app\models\value;

use Yii;

/**
 * This is the model class for table "RateClasses".
 *
 * @property string $rate_class
 * @property string $descrip
 */
class RateClass extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'RateClasses';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rate_class'], 'required'],
            [['rate_class'], 'string', 'max' => 2],
            [['descrip'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rate_class' => 'Rate Class',
            'descrip' => 'Descrip',
        ];
    }
}
