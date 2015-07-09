<?php

namespace app\controllers;

use Yii;
use app\models\project\jtp\Project;
use app\models\project\jtp\ProjectSearch;
use app\models\project\jtp\HoldAmount;
use yii\data\ActiveDataProvider;

/**
 * ProjectController implements the CRUD actions for Project model.
 */
class ProjectJtpController extends \app\controllers\project\BaseController
{
	
	protected $type = 'JTP';
	
	public function actionIndex()
	{
		
		$total_hold = HoldAmount::find()
				->joinWith(['project'])
				->where(['project_status' => 'A'])
				->sum('hold_amt')
		;
		$this->otherProviders['total_hold'] = $total_hold;
		
		return parent::actionIndex();
		
	}

    /**
     * Displays a single Project model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
    	$this->model = $this->findModel($id);
    	$this->otherProviders['paymentProvider'] = new ActiveDataProvider([
    			'query' => $this->model->getPayments(),
				'sort' => false,
    	]);

    	return parent::actionView($id);
    	
    }

}
