<?php

namespace app\modules\admin\models;

use app\helpers\OptionHelper;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "FeeTypes".
 *
 * @property string $fee_type
 * @property string $descrip
 * @property string $freq
 * @property string $short_descrip
 * @property string $extDescrip
 * @property string $is_assess [enum('T', 'F')]
 * @property string $contribution [enum('T', 'F')]
 * @property int $seq [int(11)]
 */
class FeeType extends ActiveRecord
{
	CONST TYPE_DUES = 'DU';
	CONST TYPE_REINST = 'RN';
	CONST TYPE_CC = 'CC';
	CONST TYPE_HOURS = 'HR';
	CONST TYPE_INIT = 'IN';

	CONST TYPE_IUPAT = 'IU';
	CONST TYPE_JTP = 'JT';
	CONST TYPE_PAC = 'PA';
	CONST TYPE_WAGEPCT = 'PC';
	CONST TYPE_LMCI = 'LM';
	CONST TYPE_MHWAGEPCT = 'PM';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'FeeTypes';
    }

    public static function assessmentTypes()
    {
        return [
            self::TYPE_REINST,
            self::TYPE_CC,
            self::TYPE_INIT,
        ];
    }

    public static function contributionTypes()
    {
        return [
            self::TYPE_IUPAT,
            self::TYPE_JTP,
            self::TYPE_PAC,
            self::TYPE_WAGEPCT,
            self::TYPE_LMCI,
            self::TYPE_MHWAGEPCT,
        ];
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
            [['descrip'], 'string', 'max' => 50],
        	[['is_assess'], 'in', 'range' => OptionHelper::getAllowedTF()],
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
        	'is_assess' => 'Assessible',
        ];
    }
    
    public function getExtDescrip()
    {
    	return $this->fee_type . ': ' . $this->descrip;
    }
    
    
    
}
