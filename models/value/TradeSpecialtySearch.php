<?php

namespace app\models\value;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\value\TradeSpecialty;

/**
 * TradeSpecialtySearch represents the model behind the search form about `app\models\value\TradeSpecialty`.
 */
class TradeSpecialtySearch extends TradeSpecialty
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['specialty', 'lob_cd'], 'safe'],
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
        $query = TradeSpecialty::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        	'sort'=> ['defaultOrder' => ['lob_cd'=>SORT_ASC, 'specialty' => SORT_ASC]]	
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'specialty', $this->specialty])
              ->andFilterWhere(['lob_cd' => $this->lob_cd])
        ;

        return $dataProvider;
    }
}
