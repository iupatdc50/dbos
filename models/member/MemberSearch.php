<?php

namespace app\models\member;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\member\Member;

/**
 * MemberSearch represents the model behind the search form about `app\models\member\Member`.
 */
class MemberSearch extends Member
{
	// Search place holders
	public $lob_cd;
	public $status;
	public $home_island;
	public $fullName;
	
	/**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'ssnumber', 'report_id', 'fullName', 'middle_inits', 
            		'suffix', 'birth_dt', 'gender', 
            		'shirt_size', 'local_pac', 'hq_pac', 'remarks', 
            		'lob_cd', 'status', 'home_island',], 'safe'],
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
        $query = Member::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
     		'sort'=> ['defaultOrder' => ['last_nm'=>SORT_ASC, 'first_nm' => SORT_ASC]]
        ]);
        
        $dataProvider->sort->attributes['lob_cd'] = ['asc' => ['lob_cd' => SORT_ASC], 'desc' => ['lob_cd' => SORT_DESC]];
        $dataProvider->sort->attributes['awarded_contractor'] = ['asc' => ['contractor' => SORT_ASC], 'desc' => ['contractor' => SORT_DESC]];
        $dataProvider->sort->attributes['fullName'] = ['asc' => ['last_nm' => SORT_ASC, 'first_nm' => SORT_ASC], 'desc' => ['last_nm' => SORT_DESC, 'first_nm' => SORT_DESC]];
        
        // Default set to active
		if (!isset($params['MemberSearch']['status']))
			$params['MemberSearch']['status'] = 'A';
		
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        
        $query->joinWith(['currentStatus', 'homeAddress.zipCode']);

        /*
        $dataProvider->sort->attributes['island'] = [
        		'asc' => ['ZipCodes.island' => SORT_ASC],
        		'desc' => ['ZipCodes.island' => SORT_DESC],
        ];
        */

        $query->andFilterWhere(['Members.member_id' => $this->member_id])
            ->andFilterWhere(['birth_dt' => $this->birth_dt])
        	->andFilterWhere(['lob_cd' => $this->lob_cd])
        	->andFilterWhere(['island' => $this->home_island]);

        // Accommodate boolean value in display
	  	$query->andFilterWhere(['member_status' => $this->status]);
	  	
        
        $query->andFilterWhere(['like', 'ssnumber', $this->ssnumber])
            ->andFilterWhere(['like', 'report_id', $this->report_id])
            ->andFilterWhere(['like', 'gender', $this->gender])
            ->andFilterWhere(['like', 'shirt_size', $this->shirt_size])
            ->andFilterWhere(['like', 'local_pac', $this->local_pac])
            ->andFilterWhere(['like', 'hq_pac', $this->hq_pac])
            ->andFilterWhere(['like', 'remarks', $this->remarks])
        	->andFilterWhere(['or', ['like', 'last_nm', $this->fullName], ['like', 'first_nm', $this->fullName]])
        	;
        
        return $dataProvider;
    }
}
