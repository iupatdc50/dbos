<?php

namespace app\models\value;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\value\BillRate;

/**
 * BillRateSearch represents the model behind the search form about `app\models\value\BillRate`.
 */
class BillRateSearch extends BillRate
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['lob_cd', 'member_class', 'rate_class', 'fee_type', 'effective_dt', 'end_dt'], 'safe'],
            [['rate'], 'number'],
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
        $query = BillRate::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'effective_dt' => $this->effective_dt,
            'end_dt' => $this->end_dt,
            'rate' => $this->rate,
        ]);

        $query->andFilterWhere(['like', 'lob_cd', $this->lob_cd])
            ->andFilterWhere(['like', 'member_class', $this->member_class])
            ->andFilterWhere(['like', 'rate_class', $this->rate_class])
            ->andFilterWhere(['like', 'fee_type', $this->fee_type]);

        return $dataProvider;
    }
}
