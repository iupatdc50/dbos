<?php

namespace app\models\accounting;

use Yii;

/**
 * This is the model class for table "TradeFeeTypesExt".
 *
 * @property string $lob_cd
 * @property string $fee_type
 * @property string $descrip
 * @property string $short_descrip
 * @property string $employer_remittable
 * @property string $member_remittable
 * @property string $contribution
 * 
 * @property string $colHead
 */
class TradeFeeType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'TradeFeeTypesExt';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lob_cd', 'fee_type', 'descrip'], 'required'],
            [['employer_remittable', 'member_remittable', 'contribution'], 'string'],
            [['lob_cd'], 'string', 'max' => 4],
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
            'lob_cd' => 'Lob Cd',
            'fee_type' => 'Fee Type',
            'descrip' => 'Descrip',
            'employer_remittable' => 'Employer Remittable',
            'member_remittable' => 'Member Remittable',
        	'contribution' => 'Contribution',
        ];
    }
    
   public function getColHead()
   {
   		return $this->fee_type . ': ' . $this->short_descrip;
   } 
}
