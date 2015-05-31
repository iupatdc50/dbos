<?php

namespace app\controllers\base;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * Standard extension for models manipulated by subcontrollers
 */
class RootController extends Controller
{
	
	public function actionView($id)
	{
		$this->storeReturnUrl();
	}
	
	public function actionUpdate($id)
	{
		$this->storeReturnUrl();
	}
	
	/**
     * Allows GoBack() to return to the sending page instead of the home page
     */
    protected function storeReturnUrl()
    {
        Yii::$app->user->returnUrl = Yii::$app->request->url;
    }
		
}