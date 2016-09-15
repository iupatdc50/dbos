<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ZipCodes".
 *
 * @property string $zip_cd
 * @property string $city
 * @property string $island
 * @property string $st
 *
 * @property Island $island0
 */
class ZipCode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ZipCodes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['zip_cd', 'city'], 'required'],
            [['zip_cd'], 'string', 'max' => 5],
            [['city'], 'string', 'max' => 30],
            [['island'], 'default', 'value' => null],
            ['island', 'required', 'when' => function($model) {
            	return $model->st == 'HI';
            }, 'whenClient' => "function (attribute, value) {
            	return $('#zipcode-st').val() == 'HI';
    		}"],
        	[['island'], 'exist', 'targetClass' => 'app\models\value\Island'],
            [['st'], 'string', 'max' => 2]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'zip_cd' => 'Zip Cd',
            'city' => 'City',
            'island' => 'Island',
            'st' => 'State',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIsland0()
    {
        return $this->hasOne(Island::className(), ['island' => 'island']);
    }
    
    /**
     * 
     * @param boolean $showZip When true, include Zip code in returned string
     * @return string
     */
    public function getCityLn($showZip = TRUE)
    {
    	$zip_cd = $showZip ? ' ' . $this->zip_cd : '';
    	return implode(', ', array_filter($this->getAttributes(['city', 'island', 'st']))) . $zip_cd;
    }
}
