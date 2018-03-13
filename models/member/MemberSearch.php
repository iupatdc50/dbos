<?php

namespace app\models\member;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\member\Member;
use app\models\member\Specialty;
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
	public $fullName;
	public $specialties;
	public $employer;
	
	/**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'ssnumber', 'report_id', 'fullName', 'middle_inits', 
            		'suffix', 'birth_dt', 'gender', 
            		'shirt_size', 'local_pac', 'hq_pac', 'remarks', 
            		'lob_cd', 'status', 'class', 'specialties', 'employer', 'dues_paid_thru_dt',
            		
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
        $dataProvider->sort->attributes['class'] = [
        		'asc' => ['member_class' => SORT_ASC, 'wage_percent' => SORT_ASC], 
        		'desc' => ['member_class' => SORT_DESC, 'wage_percent' => SORT_DESC],
        ];
        
        // Default set to active
		if (!isset($params['MemberSearch']['status']))
			$params['MemberSearch']['status'] = 'A';
		
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        
        $query->joinWith(['currentStatus', 'currentClass', 'specialties', 'employer.duesPayor']);

       	$criteria = CriteriaHelper::parseMixed('dues_paid_thru_dt', $this->dues_paid_thru_dt, true);
       	$query->andFilterWhere($criteria);
        
        $query->andFilterWhere(['lob_cd' => $this->lob_cd])
        	->andFilterWhere(['member_class' => $this->class])
        	->andFilterWhere(['like', 'report_id', $this->report_id])
        	->andFilterWhere([
        			'or', 
        			['like', 'last_nm', $this->fullName], 
        			['like', 'first_nm', $this->fullName], 
        			[Member::tableName() . '.member_id' => $this->fullName],
        	])
        	->andFilterWhere(['like', Specialty::tableName() . '.specialty', $this->specialties])
        ;

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
