<?php

namespace app\models\value;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\value\RateClass;

/**
 * TradeSpecialtySearch represents the model behind the search form about `app\models\value\TradeSpecialty`.
 */
class RateClassSearch extends RateClass
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rate_class', 'descrip'], 'safe'],
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
        $query = RateClass::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        	'sort'=> ['defaultOrder' => ['rate_class'=>SORT_ASC]]	
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'descrip', $this->descrip])
              ->andFilterWhere(['rate_class' => $this->rate_class])
        ;

        return $dataProvider;
    }
}
