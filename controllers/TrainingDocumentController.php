<?php

namespace app\controllers;

use Yii;
use app\controllers\document\BaseController;


class TrainingDocumentController extends BaseController
{
    public $recordClass = 'app\models\training\Document';

	public function actionSummaryJson($id)
	{
        if (!Yii::$app->user->can('manageTraining'))
            return $this->asJson($this->renderAjax('/partials/_deniedview'));
        return parent::actionSummaryJson($id);
	}

}