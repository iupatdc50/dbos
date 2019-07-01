<?php

namespace app\controllers;

use Yii;
use yii\helpers\Json;
use app\controllers\document\BaseController;


class TrainingDocumentController extends BaseController
{
    public $recordClass = 'app\models\training\Document';

	public function actionSummaryJson($id)
	{
        if (!Yii::$app->user->can('manageTraining'))
            echo Json::encode($this->renderAjax('/partials/_deniedview'));
        else
            parent::actionSummaryJson($id);
	}

}