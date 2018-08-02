<?php

namespace app\controllers;

use Yii;
use app\models\accounting\ResponsibleEmployer;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

class ResponsibleEmployerController extends \yii\web\Controller
{
    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->save()) {
                    Yii::$app->session->addFlash('success', "Submitting employer modified");
                    return $this->goBack();
                }
                throw new Exception('Problem with post.  Errors: ' . print_r($model->errors, true));
            }
        }
        return $this->renderAjax('update', compact('model'));
    }

    /**
     * Finds the ActiveRecord model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return \yii\db\ActiveRecord the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = ResponsibleEmployer::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $model;
    }

}
