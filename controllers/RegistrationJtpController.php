<?php

namespace app\controllers;

use Yii;
use app\controllers\basedoc\SubmodelController;

/**
 * Implements the CRUD actions for Registration model.
 */
class RegistrationJtpController extends SubmodelController
{
	
	/* Base controller properties - All are required */
	public $recordClass = 'app\models\project\jtp\Registration';
	public $relationAttribute = 'project_id';
	
}
