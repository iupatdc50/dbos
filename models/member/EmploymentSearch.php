<?php

namespace app\models\member;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\member\Employment;

/**
 * EmploymentSearch represents the model behind the search form about `app\models\member\Employment`.
 */
class EmploymentSearch extends Employment
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'effective_dt', 'end_dt', 'employer', 'dues_payor'], 'safe'],
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
        $query = Employment::find();

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
            'effective_dt' => $this->effective_dt,
            'end_dt' => $this->end_dt,
        ]);

        $query->andFilterWhere(['like', 'member_id', $this->member_id])
            ->andFilterWhere(['like', 'employer', $this->employer])
            ->andFilterWhere(['like', 'dues_payor', $this->dues_payor]);

        return $dataProvider;
    }
}
