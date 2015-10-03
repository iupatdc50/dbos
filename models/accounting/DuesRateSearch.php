<?php

namespace app\models\accounting;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\accounting\DuesRate;

/**
 * DuesRateSearch represents the model behind the search form about `app\models\accounting\DuesRate`.
 */
class DuesRateSearch extends DuesRate
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['lob_cd', 'rate_class', 'effective_dt', 'end_dt'], 'safe'],
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
        $query = DuesRate::find();

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
            ->andFilterWhere(['like', 'rate_class', $this->rate_class])
        ;

        return $dataProvider;
    }
}
