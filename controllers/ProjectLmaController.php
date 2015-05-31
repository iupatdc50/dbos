<?php

namespace app\controllers;

use Yii;
use app\models\project\lma\Project;
use app\models\project\lma\ProjectSearch;

/**
 * ProjectController implements the CRUD actions for Project model.
 */
class ProjectLmaController extends \app\controllers\project\BaseController
{
	public $recordClass = 'app\models\project\lma\Project';
	public $recordSearchClass = 'app\models\project\lma\ProjectSearch';
	public $registrationClass = 'app\models\project\lma\Registration';
	
	protected $type = 'LMA';
	
}
