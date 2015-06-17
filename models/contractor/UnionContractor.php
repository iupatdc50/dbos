<?php

namespace app\models\contractor;

use Yii;

/**
 * This is the model class for view UnionContractors, which flattens all active signatories
 * for a contractor into a single row with a comma separated list of LOB codes.
 *
 * @property string $license_nbr
 * @property string $lobs
 */
class UnionContractor extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'UnionContractors';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['license_nbr'], 'required'],
            [['license_nbr'], 'string', 'max' => 8],
            [['lobs'], 'string', 'max' => 24]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'license_nbr' => 'License Nbr',
            'lobs' => 'Lobs',
        ];
    }
}
