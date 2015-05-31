<?php

namespace app\models\contractor;

use Yii;

/**
 * This is the model class for table "AgreementTypes".
 *
 * @property string $agreement_type
 * @property string $descrip
 * @property string $is_ancillary
 * @property integer $renewal_years
 *
 */
class AgreementType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AgreementTypes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['agreement_type', 'descrip'], 'required'],
            [['is_ancillary'], 'string'],
            [['renewal_years'], 'integer'],
            [['agreement_type'], 'string', 'max' => 2],
            [['descrip'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'agreement_type' => 'Agreement Type',
            'descrip' => 'Description',
            'is_ancillary' => 'Is Ancillary',
            'renewal_years' => 'Renewal Years',
        ];
    }

}
