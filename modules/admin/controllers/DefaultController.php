<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class DefaultController extends Controller
{
    /**
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionIndex()
    {
        if (Yii::$app->user->can('manageSupport'))
            return $this->render('index');
        throw new ForbiddenHttpException("You are not allowed to view this page ");
    }
    
    public function actionInfo()
    {
    	return $this->render('info');
    }
    
    public function actionAbout()
    {
    	return $this->render('about');
    }
}
