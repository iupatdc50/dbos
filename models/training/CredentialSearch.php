<?php

namespace app\models\training;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\training\Credential;

/**
 * CredentialSearch represents the model behind the search form about `app\models\training\Credential`.
 */
class CredentialSearch extends Credential
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'display_seq', 'duration'], 'integer'],
            [['credential', 'card_descrip', 'catg', 'show_on_cert', 'show_on_id'], 'safe'],
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
        $query = Credential::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'display_seq' => $this->display_seq,
            'duration' => $this->duration,
        ]);

        $query->andFilterWhere(['like', 'credential', $this->credential])
            ->andFilterWhere(['like', 'card_descrip', $this->card_descrip])
            ->andFilterWhere(['like', 'catg', $this->catg])
            ->andFilterWhere(['like', 'show_on_cert', $this->show_on_cert])
            ->andFilterWhere(['like', 'show_on_id', $this->show_on_id]);

        return $dataProvider;
    }
}
