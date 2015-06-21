<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\member\Specialty;
use app\models\member\Member;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MemberSpecialtyController implements the CRUD actions for member\Specialty model
 */
class MemberSpecialtyController extends Controller
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

	public function actionCreate($relation_id)
	{
		if (($member = Member::findOne($relation_id)) == null)
			throw new \InvalidArgumentException('Invalid member ID passed: ' . $relation_id);
		/** @var ActiveRecord $model */
		$model = new Specialty(['member' => $member]);
	
		if ($model->load(Yii::$app->request->post())) {
			if ($model->save()) {
				return $this->goBack();
			}
			throw new \Exception ('Problem with post.  Errors: ' . print_r($model->errors, true));
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
		if (($model = Specialty::findOne($id)) == null) 
			throw new NotFoundHttpException('The requested page does not exist');
		$model->delete();
		return $this->goBack();
	}
	
}