<?php

namespace app\models\project;

use Yii;

/**
 * This is the model class for table "ProjectAddressesDefault".
 *
 * @property string $project_id
 * @property integer $address_id
 *
 * @property Projects $project
 * @property Address $address
 */
class AddressDefault extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ProjectAddressesDefault';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'address_id'], 'required'],
            [['address_id'], 'integer'],
            [['project_id'], 'string', 'max' => 11],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => BaseProject::className(), 'targetAttribute' => ['project_id' => 'project_id']],
            [['address_id'], 'exist', 'skipOnError' => true, 'targetClass' => Address::className(), 'targetAttribute' => ['address_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'project_id' => 'Project ID',
            'address_id' => 'Address ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(BaseProject::className(), ['project_id' => 'project_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddress()
    {
        return $this->hasOne(Address::className(), ['id' => 'address_id']);
    }
}
