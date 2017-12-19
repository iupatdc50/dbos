<?php

namespace app\models\contractor;

use Yii;
use app\models\base\BaseNote;

/**
 * This is the model class for table "ContractorNotes".
 *
 * @property integer $id
 * @property string $license_nbr
 * @property string $note
 * @property integer $created_at
 * @property integer $created_by
 *
 * @property Contractor $contractor
 */
class Note extends BaseNote
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ContractorNotes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $this->_validationRules = [
            [['license_nbr', 'note'], 'required'],
            [['license_nbr'], 'exist', 'targetClass' => '\app\models\contractor\Contractor'],
        ];
        return parent::rules();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $this->_labels = [
            'license_nbr' => 'License No',
        ];
        return parent::attributeLabels();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContractor()
    {
        return $this->hasOne(Contractor::className(), ['license_nbr' => 'license_nbr']);
    }

}