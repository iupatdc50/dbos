<?php

namespace app\models\contractor;

use app\models\value\Lob;
use app\helpers\OptionHelper;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "SignatoryAgreements".
 *
 * @property string $lob_cd
 * @property string $is_pla
 * @property string $assoc
 *
 * @property Lob $lob
 */
class Signatory extends Agreement
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'SignatoryAgreements';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
            [['lob_cd'], 'required'],
        	[['lob_cd'], 'exist', 'targetClass' => '\app\models\value\Lob'],
        	[['is_pla', 'assoc'], 'in', 'range' => OptionHelper::getAllowedTF()],
        	[['is_pla', 'assoc'], 'default', 'value' => 'F'],
        ];
        return parent::rules();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $this->_labels = [
            'lob_cd' => 'Union',
        	'is_pla' => 'Project Labor Agreemt',
        	'assoc' => 'Association Member',
        ];
        return parent::attributeLabels();
    }
    
    public function afterSave($insert, $changedAttributes)
    {
    	$this->updateContractorStatus();
    	parent::afterSave($insert, $changedAttributes);
    }
    
    public function afterDelete()
    {
    	$this->updateContractorStatus();
    	parent::afterDelete();
    }
    
    protected function updateContractorStatus() {
    	$this->contractor->setStatus();
    	$this->contractor->save();
    }
    
    /**
     * @return ActiveQuery
     */
    public function getLob()
    {
        return $this->hasOne(Lob::className(), ['lob_cd' => 'lob_cd']);
    }
    
    public function getLobOptions()
    {
    	return ArrayHelper::map(Lob::find()->orderBy('lob_cd')->all(), 'lob_cd', 'short_descrip');
    }

}
