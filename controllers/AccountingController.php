<?php

namespace app\controllers;

use app\models\accounting\ReinstateForm;
use Yii;
use \yii\web\Controller;
use yii\web\Response;
use yii\helpers\Json;

class AccountingController extends Controller
{
	
	public $layout = 'accounting';
	
    /**
     * @return mixed
     */
    public function actionIndex()
    {
        
        return $this->render('index', [
        ]);
    }
    
	public function actionReinstate() {
		$model = new ReinstateForm();
		
		echo Json::encode($this->renderAjax('reinstate', compact('model')));
		
	}
}