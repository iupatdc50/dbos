<?php

namespace app\controllers;

use app\controllers\base\SubmodelController;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MemberAddressController implements the CRUD actions for member\Address model.
 */
class MemberAddressController extends SubmodelController
{
	public $recordClass = 'app\models\member\Address';
	public $relationAttribute = 'member_id';
}
