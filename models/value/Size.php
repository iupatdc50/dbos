<?php

namespace app\models\value;

use Yii;

/**
 * This is the model class for table "Sizes".
 *
 * @property string $size_cd
 * @property string $seq
 *
 * @property Members[] $members
 */
class Size extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Sizes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['size_cd', 'seq'], 'required'],
            [['size_cd'], 'string', 'max' => 3],
            [['seq'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'size_cd' => 'Size Cd',
            'seq' => 'Seq',
        ];
    }

}
