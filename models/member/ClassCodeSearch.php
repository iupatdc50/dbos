<?php

namespace app\models\member;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ClassCodeSearch represents the search model for `MemberClassCodes`.
 */
class ClassCodeSearch extends ClassCode
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_class_cd', 'descrip'], 'safe'],
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
        $query = ClassCode::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        
        $query->andFilterWhere([
            'member_class_cd' => $this->member_class_cd,
        ]);

        $query->andFilterWhere(['like', 'descrip', $this->descrip])
        	;
        
        return $dataProvider;
    }
}
