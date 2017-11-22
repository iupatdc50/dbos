<?php
namespace app\controllers;

use app\controllers\base\SubmodelController;
use Yii;
use app\models\accounting\Assessment;
use app\models\accounting\AssessmentAllocation;
use app\models\accounting\WaiveAssessmentForm;
use app\models\member\Member;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\data\yii\data;

class AssessmentController extends SubmodelController
{
	public $recordClass = 'app\models\accounting\Assessment';
	public $relationAttribute = 'member_id';
	
/* Use this signature for expandable row.  Doesn't work when embedded in accordion
	public function actionDetailAjax()
 */
	public function actionDetailAjax($id)
	{
		$model = $this->findModel($id);
/* For expandable row
		$model = $this->findModel($_POST['expandRowKey']);
 */
		$query = AssessmentAllocation::find()->joinWith('allocatedMember')->where(['assessment_id' => $id]);
		$allocProvider = new ActiveDataProvider(['query' => $query]);
		
		return $this->renderAjax('_detail', [
				'model' => $model,
				'allocProvider' => $allocProvider,
		]);
		
	}
	
	public function actionWaive($id)
	{
		$model = new WaiveAssessmentForm;
		
		if ($model->load(Yii::$app->request->post())) {
			$model->assessment_id = $id;
		
			if($model->validate()) {
				$authUser = $model->getAuthUser();
				$assessment = $model->getAssessment();

				$label = "[{$assessment->feeType->descrip} assessment WAIVED on {$model->action_dt} by {$authUser->username}]";
				$details = (strlen($model->note) > 1) ? ': ' . $model->note : '';
				$note = new \app\models\member\Note([
						'note' => $label . $details,
				]);
				
				$member = Member::findOne($assessment->member_id);
				if($member->addNote($note) && $assessment->delete()) {
				  	Yii::$app->session->setFlash('success', "Assessment waived");
				  	return $this->goBack();
				}
				Yii::$app->session->setFlash('error', 'Could not waive assessment. Check log for details. Code `AC010`');
				Yii::error("*** AC020 Assessment waive process error.  Messages: " . print_r($model->errors, true));
			}
		}
		
		return $this->renderAjax('waive', compact('model'));
		
	}
	
}