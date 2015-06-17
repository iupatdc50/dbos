<?php

namespace app\models\contractor;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\contractor\Contractor;
use app\models\member\Employment;
use app\helpers\CriteriaHelper;

/**
 * ContractorSearch represents the model behind the search form about `app\models\contractor\Contractor`.
 */
class ContractorSearch extends Contractor
{
	// Search place holders
	public $lobs;
	public $employeeCount;
		
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lobs', 'license_nbr', 'contractor', 'contact_nm', 'email', 'is_active', 'employeeCount'], 'safe'],
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
        $query = Contractor::find();
        $empl_subquery = Employment::find()
        			->select('employer, COUNT(*) AS employee_count')
        			->where('end_dt IS NULL')
        			->groupBy('employer')
        ;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        	'sort' => ['defaultOrder' => ['contractor' => SORT_ASC]],
        ]);
        
        // Default set to active
		if (!isset($params['ContractorSearch']['is_active']))
			$params['ContractorSearch']['is_active'] = 'T';
		
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        
        $query->leftJoin([
        		'EmplCounts' => $empl_subquery,
        ], 'EmplCounts.employer = Contractors.license_nbr'); 

        $query->joinWith(['currentSignatory']);

        $query->andFilterWhere(['Contractors.license_nbr' => $this->license_nbr])
            ->andFilterWhere(['like', 'contractor', $this->contractor])
            ->andFilterWhere(['like', 'contact_nm', $this->contact_nm])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'lobs', $this->lobs])
            ->andFilterWhere(['is_active' => $this->is_active])
        ;
        
        $criteria = CriteriaHelper::parseMixed('employee_count', $this->employeeCount);
        $query->andFilterWhere($criteria);
        
        return $dataProvider;
    }
}
