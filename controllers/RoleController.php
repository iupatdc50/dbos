<?php

namespace app\controllers;

use yii\web\Controller;
use yii\data\ActiveDataProvider;
use app\models\rbac\AuthItemChild;

class RoleController extends Controller
{
	public function actionSummaryAjax()
	{
		$key = $_POST['expandRowKey'];
		$query = AuthItemChild::find()->where(['parent' => $key['item_name']]);
		$dataProvider = new ActiveDataProvider(['query' => $query]);
		return $this->renderAjax('_summary', ['dataProvider' => $dataProvider]);
	}
}