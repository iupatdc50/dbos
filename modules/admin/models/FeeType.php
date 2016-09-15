<?php

namespace app\modules\admin\models;

use Yii;

/**
 * This is the model class for table "FeeTypes".
 *
 * @property string $fee_type
 * @property string $descrip
 * @property string $freq
 * @property string $extDescrip
 */
class FeeType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'FeeTypes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fee_type', 'descrip'], 'required'],
            [['freq'], 'string'],
            [['fee_type'], 'string', 'max' => 2],
            [['descrip'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fee_type' => 'Fee Type',
            'descrip' => 'Descrip',
            'freq' => 'Freq',
        	'extDescrip' => 'Description',
        ];
    }
    
    public function getExtDescrip()
    {
    	return $this->fee_type . ': ' . $this->descrip;
    }
}
