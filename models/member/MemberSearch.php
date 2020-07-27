<?php

namespace app\models\member;

use app\models\training\CurrentMemberCredential;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\helpers\CriteriaHelper;

/**
 * MemberSearch represents the model behind the search form about `app\models\member\Member`.
 */
class MemberSearch extends Member
{
	// Search place holders
	public $lob_cd;
	public $status;
	public $class;
	public $wage_percent;
	public $fullName;
	public $specialties;
	public $employer;
	public $expiredCount;
	
	/**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'ssnumber', 'report_id', 'fullName', 'middle_inits', 
            		'suffix', 'birth_dt', 'gender', 
            		'shirt_size', 'local_pac', 'hq_pac', 'remarks', 
            		'lob_cd', 'status', 'class', 'wage_percent', 'specialties', 'employer', 'dues_paid_thru_dt',
            		'expiredCount',
            ], 'safe'],
//        	[['dues_paid_thru_dt'], 'date', 'format' => 'php:m/d/Y', 'message' => 'Invalid date'],
        		
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
        $cred_subquery = CurrentMemberCredential::find()
            ->select('member_id, COUNT(*) AS expired_count')
            ->where('expire_dt < "' . date('Y-m-d') . '"')
            ->groupBy('member_id')
        ;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
     		'sort'=> ['defaultOrder' => ['last_nm'=>SORT_ASC, 'first_nm' => SORT_ASC]]
        ]);
        
        $dataProvider->sort->attributes['lob_cd'] = ['asc' => ['lob_cd' => SORT_ASC], 'desc' => ['lob_cd' => SORT_DESC]];
        $dataProvider->sort->attributes['status'] = ['asc' => ['member_status' => SORT_ASC], 'desc' => ['member_status' => SORT_DESC]];
        $dataProvider->sort->attributes['fullName'] = [
        		'asc' => ['last_nm' => SORT_ASC, 'first_nm' => SORT_ASC], 
        		'desc' => ['last_nm' => SORT_DESC, 'first_nm' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['class'] = ['asc' => ['member_class' => SORT_ASC], 'desc' => ['member_class' => SORT_DESC]];
        $dataProvider->sort->attributes['wage_percent'] = ['asc' => ['wage_percent' => SORT_ASC], 'desc' => ['wage_percent' => SORT_DESC]];

        // Default set to active
		if (!isset($params['MemberSearch']['status']))
			$params['MemberSearch']['status'] = 'A';
		
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        
        $query->joinWith(['currentStatus', 'currentClass', 'qualifiesForIncrease', 'specialties', 'employer.duesPayor']);
        $query->leftJoin(['CredCounts' => $cred_subquery], 'CredCounts.member_id = Members.member_id');

       	$criteria = CriteriaHelper::parseMixed('dues_paid_thru_dt', $this->dues_paid_thru_dt, true);
       	$query->andFilterWhere($criteria);
        $criteria = CriteriaHelper::parseMixed('expired_count', $this->expiredCount);
        $query->andFilterWhere($criteria);


        $query->andFilterWhere(['lob_cd' => $this->lob_cd])
        	->andFilterWhere(['member_class' => $this->class])
        	->andFilterWhere(['like', 'report_id', $this->report_id])
        	->andFilterWhere([
        			'or', 
        			['like', 'last_nm', $this->fullName], 
        			['like', 'first_nm', $this->fullName],
        			['like', 'nick_nm', $this->fullName],
        			[Member::tableName() . '.member_id' => $this->fullName],
        	])
        	->andFilterWhere(['like', Specialty::tableName() . '.specialty', $this->specialties])
        ;

        $wage_cond = (strtolower($this->wage_percent) == 'q') ? ['>', 'should_be', 0] : CriteriaHelper::parseMixed('MC.wage_percent', $this->wage_percent);
        $query->andFilterWhere($wage_cond);

        if (strtolower($this->employer) == 'unemployed')
            $query->andFilterWhere(['empl_status' => 'U']);
        else
            $query->andFilterWhere(['like', 'contractor', $this->employer]);
        
        if ($this->status == CriteriaHelper::TOKEN_NOTSET)
        	$query->andWhere(['member_status' => null]);
        else 
        	$query->andFilterWhere(['member_status' => $this->status]);

        return $dataProvider;
    }
}
