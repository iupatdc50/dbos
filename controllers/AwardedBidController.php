<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\project\AwardedBid;

class AwardedBidController extends Controller
{
	public function actionAward($project_id)
	{
		$model = new AwardedBid();
		
		if ($model->load(Yii::$app->request->post())) {
			$model->project_id = $project_id;
			
			if (($existing = AwardedBid::findOne($project_id)) !== null) {
				$existing->registration_id = $model->registration_id;
				$existing->start_dt = $model->start_dt;
				$existing->save();
			} else {
				$model->save();
			}
			return $this->goback();
		}
		
		return $this->renderAjax('award', compact('model'));
	}
}