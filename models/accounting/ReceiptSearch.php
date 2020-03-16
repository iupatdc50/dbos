<?php

namespace app\models\accounting;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\helpers\CriteriaHelper;

/**
 * ReceiptSearch represents the model behind the search form about `app\models\accounting\Receipt`.
 */
class ReceiptSearch extends Receipt
{
	
	public $payor_type_filter;
	public $feeTypes;
	
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by'], 'integer'],
            [['payor_nm', 'payment_method', 'payor_type_filter', 'received_dt', 'feeTypes', 'remarks', 'lob_cd'], 'safe'],
            [['received_amt', 'unallocated_amt'], 'number'],
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param $mine_only
     * @return ActiveDataProvider
     */
    public function search($params, $mine_only)
    {
        $query = Receipt::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        	'sort' => ['defaultOrder' => ['received_dt' => SORT_DESC, 'id' => SORT_DESC,]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

//        $query->joinWith(['feeTypes']);

        $query->andFilterWhere([
            'id' => $this->id,
            'received_amt' => $this->received_amt,
            'unallocated_amt' => $this->unallocated_amt,
        ]);

       	$criteria = CriteriaHelper::parseMixed('received_dt', $this->received_dt, true);
       	$query->andFilterWhere($criteria);
        
        $query->andFilterWhere(['like', 'payor_nm', $this->payor_nm])
            ->andFilterWhere(['like', 'payment_method', $this->payment_method])
            ->andFilterWhere(['like', 'payor_type', $this->payor_type_filter])
//            ->andFilterWhere(['like', ReceiptFeeType::tableName() . '.fee_type', $this->feeTypes])
            ->andFilterWhere(['lob_cd' => $this->lob_cd])
//            ->andFilterWhere(['like', 'remarks', $this->remarks])
        ;
        
        if ($mine_only)
        	$query->andWhere(['created_by' => Yii::$app->user->id]);
    

        return $dataProvider;
    }
}
