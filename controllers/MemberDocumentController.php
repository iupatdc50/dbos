<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use app\models\member\Document;
use app\models\member\Member;

class MemberDocumentController extends Controller
{
	
	public function behaviors()
	{
		return [
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'delete' => ['post'],
				],
			],
		];
	}
	
	public function actionSummaryJson($id)
	{
		$query = Document::find()
			->where(['member_id' => $id])
			->orderBy('doc_type asc');
	
		$dataProvider = new ActiveDataProvider([
				'query' => $query,
				'pagination' => ['pageSize' => 5],
				'sort' => false,
		]);
	
		echo Json::encode($this->renderAjax('_summary', ['dataProvider' => $dataProvider, 'id' => $id]));
	}
	
	/**
	 * Creates a new ActiveRecord model.
	 * If creation is successful, the browser will be redirected to the primary model's 'view' page.
	 * @return mixed
	 */
	public function actionCreate($relation_id)
	{
		if (($member = Member::findOne($relation_id)) == null)
			throw new \InvalidArgumentException('Invalid member ID passed: ' . $relation_id);
		/** @var ActiveRecord $model */
		$model = new Document(['member' => $member]);
	
		if ($model->load(Yii::$app->request->post())) {
			// Prepopulate referencing column
			$image = $model->uploadImage();
			if	($model->save()) {
				if ($image !== false) {
					$path = $model->imagePath;
					$image->saveAs($path);
				}
				return $this->goBack();
			}
		}
		return $this->renderAjax('create', compact('model'));
	}

	/**
	 * Deletes an existing ActiveRecord model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionDelete($id)
	{
		if (($model = Document::findOne($id)) == null)
			throw new NotFoundHttpException('The requested page does not exist');
	    if ($model->delete()) {
        	if (!$model->deleteImage())	
        		Yii::$app->session->setFlash('error', 'Could not delete document');
        }
		return $this->goBack();
	}
	
}