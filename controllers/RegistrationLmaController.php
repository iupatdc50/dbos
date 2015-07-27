<?php

namespace app\controllers;

use Yii;
use app\controllers\basedoc\SubmodelController;

/**
 * Implements the CRUD actions for Registration model.
 */
class RegistrationLmaController extends SubmodelController
{
	
	/* Base controller properties - All are required */
	public $recordClass = 'app\models\project\lma\Registration';
	public $relationAttribute = 'project_id';
	
	public function actionCreateMaint($relation_id)
	{
		/** @var ActiveRecord $model */
		$model = new $this->recordClass(['is_maint' => true]);
		
		if ($model->load(Yii::$app->request->post())) {
			// Prepopulate referencing column
			$model->project_id = $relation_id;
			if ($model->save()) {
				return $this->goBack();
			}
			throw new Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
		}
		return $this->renderAjax('create', compact('model'));
		
	}
	
}
