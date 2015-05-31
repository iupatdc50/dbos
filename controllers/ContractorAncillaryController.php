<?php

namespace app\controllers;

use app\controllers\basedoc\SummaryController;
use Yii;
use app\models\contractor\Address;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ContractorAncillaryController implements the CRUD actions for Ancillary model.
 */
class ContractorAncillaryController extends SummaryController
{
    public $summOrder = 'signed_dt desc';
	public $recordClass = 'app\models\contractor\Ancillary';
	public $relationAttribute = 'license_nbr';
	
}
