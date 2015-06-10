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
	// Search place holder
	public $fullName;
	public $employer_search;
	
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'fullName', 'effective_dt', 'end_dt', 'employer', 'dues_payor', 'is_loaned'], 'safe'],
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
        $query = Employment::find()->joinWith(['member'])->where(['end_dt' => null]);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
     		'sort'=> ['defaultOrder' => ['fullName' =>SORT_ASC]],
    		'pagination' => ['pageSize' => 15],
        ]);

        $dataProvider->sort->attributes['fullName'] = [
        		'asc' => ['last_nm' => SORT_ASC, 'first_nm' => SORT_ASC], 
        		'desc' => ['last_nm' => SORT_DESC, 'first_nm' => SORT_DESC],
        		'default' => SORT_ASC,
        ];
        
        if (isset($this->employer_search)) {
        	$query->andFilterWhere(['or',
        		['and', ['is_loaned' => 'F', 'employer' => $this->employer_search]],	
        		['and', ['is_loaned' => 'T', 'dues_payor' => $this->employer_search]],	
        	]);
        }
        
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        
        $query->andFilterWhere(['or', ['like', 'last_nm', $this->fullName], ['like', 'first_nm', $this->fullName]]);
        $query->andFilterWhere(['is_loaned' => $this->is_loaned]);
        
        return $dataProvider;
    }
}
