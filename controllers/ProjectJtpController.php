<?php

namespace app\controllers;

use Yii;
use app\models\project\jtp\Project;
use app\models\project\jtp\ProjectSearch;
use yii\data\ActiveDataProvider;

/**
 * ProjectController implements the CRUD actions for Project model.
 */
class ProjectJtpController extends \app\controllers\project\BaseController
{
	public $recordClass = 'app\models\project\jtp\Project';
	public $recordSearchClass = 'app\models\project\jtp\ProjectSearch';
	public $registrationClass = 'app\models\project\jtp\Registration';

	protected $type = 'JTP';
	

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
