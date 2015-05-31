<?php

namespace app\controllers;

use app\controllers\base\SummaryController;
use Yii;
use app\models\member\MemberClass;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MemberClassController implements the CRUD actions for MemberClass model.
 */
class MemberClassController extends SummaryController
{
	public $recordClass = 'app\models\member\MemberClass';
	public $relationAttribute = 'member_id';
		
}
