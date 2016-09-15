<?php

namespace app\models\accounting;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\accounting\Receipt;
use app\models\accounting\ResponsibleEmployer;

/**
 * Because the contractor's receipt list is built by employer, ResponsibleEmployer is used as
 * the parent model
 */
class ReceiptContractorSearch extends ResponsibleEmployer
{
	public $received_dt;
	public $feeTypes;
	
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receipt_id'], 'integer'],
            [['received_dt', 'feeTypes', ], 'safe'],
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
    					'recordClass' => 'app\models\accounting\ResponsibleEmployer',
    					'search_attrs' => [
    							['op' => 'equal', 'col' => 'license_nbr', 'val' => 'license_nbr'],
    					],
    			],
    	];
    }
    

}
