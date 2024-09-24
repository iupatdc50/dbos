<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ZipCodeSearch represents the model behind the search form about `app\models\ZipCode`.
 */
class ZipCodeSearch extends ZipCode
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['zip_cd', 'city', 'island', 'st'], 'safe'],
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
        $query = ZipCode::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'zip_cd', $this->zip_cd])
            ->andFilterWhere(['like', 'city', $this->city])
            ->andFilterWhere(['like', 'island', $this->island])
            ->andFilterWhere(['like', 'st', $this->st]);

        return $dataProvider;
    }
}
