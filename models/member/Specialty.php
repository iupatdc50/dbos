<?php

namespace app\models\member;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\value\Lob;
use app\models\value\TradeSpecialty;

/**
 * This is the model class for table "MemberSpecialties".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $specialty
 *
 * @property TradeSpecialty $tradeSpecialty
 */
class Specialty extends \yii\db\ActiveRecord
{
	/*
	 * Holds trade for dependent dropdown
	 */
	public $trade;
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberSpecialties';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'specialty'], 'required'],
            [['member_id'], 'string', 'max' => 11],
			// Allows for client validation, 'exist' core validator does not
        	[['specialty'], 'in', 'range' => TradeSpecialty::find()->select('specialty')->asArray()->column()],
        	[['trade'], 'safe'],
            [['member_id', 'specialty'], 'unique', 'targetAttribute' => ['member_id', 'specialty'], 'message' => 'The combination of Member ID and Specialty has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'specialty' => 'Specialty',
        ];
    }
    
    public function getTradeOptions()
    {
    	return ArrayHelper::map(Lob::find()->orderBy('lob_cd')->all(), 'lob_cd', 'short_descrip');
    }
    
    public function getSpecialtyOptions()
    {
    	return ArrayHelper::map(TradeSpecialty::find()->all(), 'specialty', 'specialty');
    }

}
