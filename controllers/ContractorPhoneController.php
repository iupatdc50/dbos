<?php

namespace app\controllers;

use app\controllers\base\SubmodelController;
use Yii;
use app\models\contractor\Phone;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ContractorAddressController implements the CRUD actions for ContractorAddress model.
 */
class ContractorPhoneController extends SubmodelController
{
	public $recordClass = 'app\models\contractor\Phone';
	public $relationAttribute = 'license_nbr';
}
