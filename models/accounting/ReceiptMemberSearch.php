<?php

namespace app\models\accounting;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\accounting\Receipt;
use app\models\accounting\AllocatedMember;

/**
 * Because the member's receipt list is built by member, the AllocatedMember model is used as
 * the parent model
 */
class ReceiptMemberSearch extends AllocatedMember
{
	public $received_dt;
	public $payor_type_filter;
	public $feeTypes;
	
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receipt_id'], 'integer'],
            [['payor_type_filter', 'received_dt', 'feeTypes', ], 'safe'],
//            [['totalAllocation'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }
    
    /**
     * Builds search data provider
     *
     * @see \yii\base\Component::behaviors()
     */
    public function behaviors()
    {
    	return [
    			[
    					'class' => \app\components\behaviors\OpReceiptSearchBehavior::className(),
    					'recordClass' => 'app\models\accounting\AllocatedMember',
    					'search_attrs' => [
    							['op' => 'equal', 'col' => 'member_id', 'val' => 'member_id'],
    							['op' => 'like', 'col' => 'payor_type', 'val' => 'payor_type_filter'],
    					],
    			],
    	];
    }

}
