<?php

namespace app\controllers;

use app\controllers\base\SubmodelController;
use Yii;

/**
 * AllocatedMemberController implements the CRUD actions for accouting\AllocatedMember model.
 */
class AllocatedMemberController extends SubmodelController
{
	public $recordClass = 'app\models\accounting\AllocatedMember';
	public $relationAttribute = 'receipt_id';
}
