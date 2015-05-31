<?php

namespace app\controllers;

use app\controllers\base\SubmodelController;
use Yii;
use app\models\contractor\Address;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProjectAddressController implements the CRUD actions for ProjectAddress model.
 */
class ProjectAddressController extends SubmodelController
{
	public $recordClass = 'app\models\project\Address';
	public $relationAttribute = 'project_id';
}
