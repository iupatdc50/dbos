<?php

namespace app\modules\admin\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TradeFeeSearch represents the model behind the search form about `app\modules\admin\models\TradeFee`.
 */
class ContributionSearch extends Contribution
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lob_cd', 'contrib_type', 'wage_pct', 'factor', 'operand'], 'safe'],
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
        $query = Contribution::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['lob_cd'=>SORT_ASC, 'contrib_type'=>SORT_ASC, 'wage_pct'=>SORT_ASC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'lob_cd', $this->lob_cd])
            ->andFilterWhere(['like', 'contrib_type', $this->contrib_type])
            ->andFilterWhere(['like', 'wage_pct', $this->wage_pct])
            ->andFilterWhere(['like', 'factor', $this->factor])
            ->andFilterWhere(['like', 'operand', $this->operand])
        ;

        return $dataProvider;
    }
}
