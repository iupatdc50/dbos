<?php

namespace app\models\member;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * MemberLoginSearchModel represents the model behind the search form about `app\models\user\User`.
 */
class MemberLoginSearch extends MemberLogin
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['member_id', 'username', 'email', 'last_nm', 'first_nm'], 'safe'],
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
        $query = MemberLogin::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        
        $query->andFilterWhere([
            'id' => $this->id,
            'member_id' => $this->member_id,
            'username' => $this->username,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'email', $this->email])
        	->andFilterWhere(['like', 'first_nm', $this->first_nm])
        	->andFilterWhere(['like', 'last_nm', $this->last_nm])
        	;
        
        return $dataProvider;
    }
}
