<?php

namespace app\models\value;

use Yii;

/**
 * This is the model class for table "States".
 *
 * @property string $st
 * @property string $state_nm
 */
class State extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'States';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['st', 'state_nm'], 'required'],
            [['st'], 'string', 'max' => 2],
            [['state_nm'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'st' => 'St',
            'state_nm' => 'State Nm',
        ];
    }
}
