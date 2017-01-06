<?php

namespace app\controllers;

use yii\base\Controller;
class MaintenanceController extends Controller{
	
	public function actionIndex()
	{
		return $this->renderPartial('index');
	}
	
}