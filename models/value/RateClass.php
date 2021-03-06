<?php

namespace app\models\value;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "RateClasses".
 *
 * @property string $rate_class
 * @property string $descrip
 */
class RateClass extends ActiveRecord
{
    const RC_REGULAR = 'R';
    const RC_RETIREE = 'LE';
    const RC_LIFETIME_LP = 'LP';
    const RC_LIFETIME_LR = 'LR';

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
