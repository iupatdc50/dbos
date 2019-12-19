<?php

namespace app\models\value;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DocumentTypeSearch represents the model behind the search form about `app\models\value\DocumentType`.
 */
class DocumentTypeSearch extends DocumentType
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['doc_type', 'catg'], 'safe'],
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
        $query = DocumentType::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        	'sort'=> ['defaultOrder' => ['doc_type'=>SORT_ASC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'doc_type', $this->doc_type])
              ->andFilterWhere(['catg' => $this->catg])
        ;

        return $dataProvider;
    }
}
