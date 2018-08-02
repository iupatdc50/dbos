<?php

namespace app\controllers;

use app\models\accounting\DuesAllocation;
use Yii;
use app\models\accounting\AllocatedMember;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class AllocatedMemberController extends Controller
{
    /**
     * @param $id
     * @return string
     * @throws \Exception
     * @throws NotFoundHttpException
     */
    public function actionReassign($id)
    {
        $model = $this->findModel($id);

        /* @var $model AllocatedMember */
        if ($model->load(Yii::$app->request->post())) {

            $allocs = $model->allocations;
            foreach ($allocs as $alloc) {
                $alloc->backOutMemberStatus();
                if ($alloc instanceof DuesAllocation) {
                    $alloc->backOutDuesThru(true);
                    $alloc->save();
                }
            }

            if (!$model->save())
                throw new Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));

            $this->goBack();

        }

        return $this->renderAjax('reassign', compact('model'));
    }

    /**
     * @param $id
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!$model->delete())
            throw new Exception('Problem with delete.  Errors: ' . print_r($model->errors, true));

        $this->goBack();
    }

    /**
     * @param integer $id
     * @return \yii\db\ActiveRecord the loaded model
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = AllocatedMember::findOne($id);
        if (!$model)
            throw new NotFoundHttpException('The requested page does not exist.');
        return $model;
    }
}