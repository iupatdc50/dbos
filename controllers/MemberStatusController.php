<?php

namespace app\controllers;

use app\controllers\base\SummaryController;
use Yii;
use app\models\member\Status;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MemberStatusController implements the CRUD actions for Status model.
 */
class MemberStatusController extends SummaryController
{

	public $recordClass = 'app\models\member\Status';
	public $relationAttribute = 'member_id';
	
}
