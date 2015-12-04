<?php

namespace app\models\accounting;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\accounting\Receipt;

/**
 * ReceiptSearch represents the model behind the search form about `app\models\accounting\Receipt`.
 */
class ReceiptSearch extends Receipt
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by'], 'integer'],
            [['payor_nm', 'payment_method', 'payor_type', 'received_dt'], 'safe'],
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Receipt::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'received_dt' => $this->received_dt,
            'received_amt' => $this->received_amt,
            'unallocated_amt' => $this->unallocated_amt,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
        ]);

        $query->andFilterWhere(['like', 'payor_nm', $this->payor_nm])
            ->andFilterWhere(['like', 'payment_method', $this->payment_method])
            ->andFilterWhere(['like', 'payor_type', $this->payor_type]);

        return $dataProvider;
    }
}
