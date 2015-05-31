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
class MemberPhoneController extends SubmodelController
{
	public $recordClass = 'app\models\member\Phone';
	public $relationAttribute = 'member_id';
}
