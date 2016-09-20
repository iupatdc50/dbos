<?php
namespace app\controllers;

use app\controllers\base\SubmodelController;
use Yii;
use app\models\accounting\Assessment;
use app\models\accounting\AssessmentAllocation;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\data\yii\data;

class AssessmentController extends SubmodelController
{
	public $recordClass = 'app\models\accounting\Assessment';
	public $relationAttribute = 'member_id';
	
	public function actionDetailAjax($id)
	{
		$model = $this->findModel($id);
		$query = AssessmentAllocation::find()->joinWith('allocatedMember')->where(['assessment_id' => $id]);
		$allocProvider = new ActiveDataProvider(['query' => $query]);
		
		return $this->renderAjax('_detail', [
				'model' => $model,
				'allocProvider' => $allocProvider,
		]);
		
	}
	
}