<?php

namespace app\models\contractor;

use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "AncillaryAgreements".
 *
 * @property string $agreement_type
 *
 * @property AgreementType $agreementType
 */
class Ancillary extends Agreement
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AncillaryAgreements';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
            [['agreement_type'], 'required'],
            [['agreement_type'], 'exist', 'targetClass' => '\app\models\contractor\AgreementType'],
        ];
        return parent::rules();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $this->_labels = [
            'agreement_type' => 'Agreement',
        ];
        return parent::attributeLabels();
    }

    /**
     * @return ActiveQuery
     */
    public function getAgreementType()
    {
    	return $this->hasOne(AgreementType::className(), ['agreement_type' => 'agreement_type']);
    }

    public function getTypeOptions()
    {
    	return ArrayHelper::map(AgreementType::find()->where(['is_ancillary' => 'T'])->all(), 'agreement_type', 'descrip');
    }
    
    
    
}
