<?php

namespace app\controllers;

use app\models\accounting\Document;
use app\models\accounting\Receipt;
use InvalidArgumentException;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

class ReceiptDocumentController extends Controller
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
	
    /**
     * Creates a new ActiveRecord model.
     * If creation is successful, the browser will be redirected to the primary model's 'view' page.
     * @param $relation_id
     * @return string|Response
     */
	public function actionCreate($relation_id)
	{
		if (($receipt = Receipt::findOne($relation_id)) == null)
			throw new InvalidArgumentException('Invalid receipt ID passed: ' . $relation_id);

		$model = new Document(['receipt' => $receipt]);
	
		if ($model->load(Yii::$app->request->post())) {
			// Prepopulate referencing column
			$image = $model->uploadImage();
			if	($model->save()) {
				if ($image != false) {
					$path = $model->imagePath;
					$image->saveAs($path);
				}
				return $this->goBack();
			}
		}
        /** @noinspection MissedViewInspection */
        return $this->renderAjax('create', compact('model'));
	}

    /**
     * Deletes an existing ActiveRecord model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
	public function actionDelete($id)
	{
	    $model = Document::findOne($id);
		if (!$model)
			throw new NotFoundHttpException('The requested page does not exist');
	    if ($model->delete()) {
        	if (!$model->deleteImage())	
        		Yii::$app->session->setFlash('error', 'Could not delete document');
        }
		return $this->goBack();
	}
	
}