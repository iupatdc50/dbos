<?php

namespace app\controllers;

use Exception;
use InvalidArgumentException;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\web\Controller;
use app\models\member\Email;
use app\models\member\Member;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * MemberEmailController implements the CRUD actions for member\Email model
 */
class MemberEmailController extends Controller
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
     * @param $relation_id
     * @return string|Response
     * @throws Exception
     */
	public function actionCreate($relation_id)
	{
		if (($member = Member::findOne($relation_id)) == null)
			throw new InvalidArgumentException('Invalid member ID passed: ' . $relation_id);
        $model = new Email([
		    'scenario' => Email::SCENARIO_MEMBEREXISTS,
            'member' => $member,
        ]);
	
		if ($model->load(Yii::$app->request->post())) {
			if ($model->save()) {
				Yii::$app->session->setFlash('success', "Email entry created");
				return $this->goBack();
			}
			throw new Exception ('Problem with post.  Errors: ' . print_r($model->errors, true));
		}
		return $this->renderAjax('create', compact('model'));
	
	}

    /**
     * Deletes an existing ActiveRecord model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws Exception
     * @throws StaleObjectException
     * @throws Throwable
     */
	public function actionDelete($id)
	{
		if (($model = Email::findOne($id)) == null) 
			throw new NotFoundHttpException('The requested page does not exist');
		$model->delete();
    	Yii::$app->session->setFlash('success', "Email entry deleted");
		return $this->goBack();
	}
	
}