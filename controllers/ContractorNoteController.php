<?php

namespace app\controllers;

use Yii;
use app\models\contractor\Note;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ContractorNoteController implements the CRUD actions for Note model.
 */
class ContractorNoteController extends Controller
{
	public $recordClass = 'app\models\contractor\Note';
	
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
     * Deletes an existing Note model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        if (Yii::$app->user->can('manageCJournal')) {
            $model = $this->findModel($id);
        
            if (isset($model->doc_id))
                $model->deleteImage();
            $model->delete();

            return $this->goBack();
        }

        throw new ForbiddenHttpException("You are not allowed to perform this action");
    }

    /**
     * Finds the Note model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Note the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = call_user_func([$this->recordClass, 'findOne'], $id);
        if (!$model) {
        	throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $model;
    }
}
