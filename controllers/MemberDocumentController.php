<?php

namespace app\controllers;

use Yii;
use app\controllers\document\BaseController;


class MemberDocumentController extends BaseController
{
    public $recordClass = 'app\models\member\Document';

	public function actionSummaryJson($id)
	{
        if (!Yii::$app->user->can('browseMemberExt'))
            return $this->asJson($this->renderAjax('/partials/_deniedview'));
        return parent::actionSummaryJson($id);
	}

}