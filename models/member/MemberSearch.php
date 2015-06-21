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
	public $specialties;
	
	/**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'ssnumber', 'report_id', 'fullName', 'middle_inits', 
            		'suffix', 'birth_dt', 'gender', 
            		'shirt_size', 'local_pac', 'hq_pac', 'remarks', 
            		'lob_cd', 'status', 'home_island', 'specialties'], 'safe'],
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
        $dataProvider->sort->attributes['fullName'] = ['asc' => ['last_nm' => SORT_ASC, 'first_nm' => SORT_ASC], 'desc' => ['last_nm' => SORT_DESC, 'first_nm' => SORT_DESC]];
        
        // Default set to active
		if (!isset($params['MemberSearch']['status']))
			$params['MemberSearch']['status'] = 'A';
		
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        
        $query->joinWith(['currentStatus', 'homeAddress.zipCode', 'specialties']);

        $dataProvider->sort->attributes['home_island'] = [
        		'asc' => ['ZipCodes.island' => SORT_ASC],
        		'desc' => ['ZipCodes.island' => SORT_DESC],
        ];

        $query->andFilterWhere(['lob_cd' => $this->lob_cd])
        	->andFilterWhere(['member_status' => $this->status])
        	->andFilterWhere(['Members.member_id' => $this->member_id])
        	->andFilterWhere(['like', 'ssnumber', $this->ssnumber])
        	->andFilterWhere(['or', ['like', 'last_nm', $this->fullName], ['like', 'first_nm', $this->fullName]])
        	->andFilterWhere(['island' => $this->home_island])
        	->andFilterWhere(['like', 'MemberSpecialties.specialty', $this->specialties])
        ;

        
        return $dataProvider;
    }
}
