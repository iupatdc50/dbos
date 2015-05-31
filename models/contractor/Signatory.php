<?php

namespace app\models\contractor;

use Yii;
use app\models\value\Lob;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "SignatoryAgreements".
 *
 * @property string $lob_cd
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
        ];
        return parent::attributeLabels();
    }

    /**
     * @return \yii\db\ActiveQuery
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
