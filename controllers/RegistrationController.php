<?php

namespace app\controllers;

use app\controllers\basedoc\SummaryController;

/**
 * Special summary controller for general Registration models by bidder.
 */
class RegistrationController extends SummaryController
{

	/* Base controller properties - All are required */
	public $recordClass = 'app\models\project\BaseRegistration';
	public $relationAttribute = 'bidder';
	
	/* Summary controller properties */
	public $summJoinWith = ['project', 'isAwarded'];
	public $summWhere = ['project_status' => 'A'];
	public $summOrder = 'bid_dt desc';
	public $summPageSize = 15;
	
	
}