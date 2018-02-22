<?php

namespace app\controllers;

use app\controllers\basedoc\SummaryController;

/**
 * ContractorBillController implements the CRUD actions for GeneratedBill model.
 */
class ContractorBillController extends SummaryController
{
    public $summOrder = 'created_at desc';
	public $recordClass = 'app\models\accounting\GeneratedBill';
	public $relationAttribute = 'license_nbr';
}
