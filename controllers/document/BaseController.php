<?php

namespace app\controllers\document;

use app\models\base\BaseDocument;
use InvalidArgumentException;
use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use app\models\member\Member;

class BaseController extends Controller
{
    /** @var string Name of the Document class to be manipulated */
    public $recordClass;


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
	    $query = call_user_func([$this->recordClass, 'find']);
        $query->where(['member_id' => $id])
              ->orderBy('doc_type asc');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 5],
            'sort' => false,
        ]);

        /** @noinspection MissedViewInspection */
        echo Json::encode($this->renderAjax('_summary', ['dataProvider' => $dataProvider, 'id' => $id]));
	}

    /**
     * Creates a new ActiveRecord model.
     * If creation is successful, the browser will be redirected to the primary model's 'view' page.
     * @param $relation_id
     * @return mixed
     */
	public function actionCreate($relation_id)
	{
		if (($member = Member::findOne($relation_id)) == null)
			throw new InvalidArgumentException('Invalid member ID passed: ' . $relation_id);

		/** @var BaseDocument $model */
		$model = new $this->recordClass(['member' => $member]);
	
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
        /** @noinspection MissedViewInspection */
        return $this->renderAjax('create', compact('model'));
	}

    /**
     * Deletes an existing ActiveRecord model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
	public function actionDelete($id)
	{
	    $model = call_user_func([$this->recordClass, 'findOne'], $id);
		if (!$model)
			throw new NotFoundHttpException('The requested page does not exist');
	    if ($model->delete()) {
        	if (!$model->deleteImage())	
        		Yii::$app->session->setFlash('error', 'Could not delete document');
        }
		return $this->goBack();
	}
	
}