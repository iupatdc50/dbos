<?php

namespace app\controllers;

use Yii;
use app\controllers\basedoc\SubmodelController;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * Implements the CRUD actions for Registration model.
 */
class RegistrationJtpController extends SubmodelController
{
	
	/* Base controller properties - All are required */
	public $recordClass = 'app\models\project\jtp\Registration';
	public $relationAttribute = 'project_id';

	public function behaviors()
	{
		return [
				'verbs' => [
						'class' => VerbFilter::className(),
						'actions' => [
								'delete' => ['post'],
						],
				],
	
				'access' => [
						'class' => AccessControl::className(),
						'only' => ['create', 'update', 'delete'],
						'rules' => [
								[
										'allow' => true,
										'actions' => ['create', 'update'],
										'roles' => ['manageProject'],
								],
								[
										'allow' => true,
										'actions' => ['delete'],
										'roles' => ['deleteProject'],
								],
						],
				],
		];
	}
	
	
	
}
