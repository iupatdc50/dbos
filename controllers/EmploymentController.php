<?php

namespace app\controllers;

use app\controllers\base\SummaryController;
use Yii;
use app\models\member\Employment;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * EmploymentController implements the CRUD actions for Employment model.
 */
class EmploymentController extends SummaryController
{
	public $recordClass = 'app\models\member\Employment';
	public $relationAttribute = 'member_id';
	
	public function actionUpdate($id)
	{
		throw new NotFoundHttpException('Non-supported feature.  Cannot update employment this way.');
	}
	
	/**
	 * Replaces the inherited controller actionDelete with a different signature
	 * 
	 * @param string $member_id
	 * @param string $effective_dt
	 * @throws NotFoundHttpException  If the model is not found
	 * @return \yii\web\Response
	 */
	public function actionRemove($member_id, $effective_dt)
	{
		$model = Employment::findOne(['member_id' => $member_id, 'effective_dt' => $effective_dt]);
		if ($model !== null) {
			$model->delete();
			return $this->goBack();
		}
		throw new NotFoundHttpException('Unable to locate employment record.');
	}
	
	public function actionLoan($relation_id)
	{
		/** @var ActiveRecord $model */
		$model = new $this->recordClass;
		// Prepopulate referencing column
		$model->{$this->relationAttribute} = $relation_id;
		// Assumptions
		$model->employer = $this->findCurrent($relation_id)->employer;
		$model->is_loaned = 'T';
		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			return $this->goBack();
		}
		
		return $this->renderAjax('loan', compact('model'));
	}
	
	public function actionTerminate($relation_id)
	{
		$model = $this->findCurrent($relation_id);
		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			return $this->goBack();
		}
		return $this->renderAjax('terminate', compact('model'));
	}
	
	protected function findCurrent($id)
	{
		return Employment::find()->where(['member_id' => $id])->andWhere(['end_dt' => null])->one();
	}
}
