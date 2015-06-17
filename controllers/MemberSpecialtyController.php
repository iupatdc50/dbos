<?php

namespace app\controllers;

use app\controllers\base\SubmodelController;
use app\models\value\TradeSpecialty;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * MemberSpecialtyController implements the CRUD actions for member\Specialty model
 */
class MemberSpecialtyController extends SubmodelController
{
	public $recordClass = 'app\models\member\Specialty';
	public $relationAttribute = 'member_id';
	
	public function actionSpecialty() {
		$out = [];
		if (isset($_POST['depdrop_parents'])) {
			$parents = $_POST['depdrop_parents'];
			if ($parents != null) {
				$lob_cd = $parents[0];
				$out = ArrayHelper::map(TradeSpecialty::find()->where(['lob_cd' => $lob_cd])->orderBy('specialty')->all(), 'specialty', 'specialty');
				echo Json::encode(['output'=>$out, 'selected'=>'']);
				return;
			}
		}
		echo Json::encode(['output'=>'', 'selected'=>'']);
	}
	
	
}