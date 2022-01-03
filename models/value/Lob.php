<?php

namespace app\models\value;


use yii\db\ActiveRecord;

/**
 * This is the model class for table "Lobs".
 *
 * @property string $lob_cd
 * @property string $descrip
 * @property string $hq_rate
 * @property string $pac_factor
 * @property integer $region
 * @property string $short_descrip
 */
class Lob extends ActiveRecord
{
    CONST TRADE_FL = '1926';
    CONST TRADE_GL = '1889';
    CONST TRADE_PT = '1791';
    CONST TRADE_TP = '1944';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Lobs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lob_cd', 'descrip', 'hq_rate', 'pac_factor', 'region'], 'required'],
            [['hq_rate', 'pac_factor'], 'number'],
            [['region'], 'integer'],
            [['lob_cd'], 'string', 'max' => 4],
            [['descrip'], 'string', 'max' => 60],
            [['short_descrip'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'lob_cd' => 'Lob Cd',
            'descrip' => 'Descrip',
            'hq_rate' => 'Hq Rate',
            'pac_factor' => 'Pac Factor',
            'region' => 'Region',
            'short_descrip' => 'Descrip',
        ];
    }
}
