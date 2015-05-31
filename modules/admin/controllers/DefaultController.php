<?php

namespace app\modules\admin\controllers;

use yii\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
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
