<?php

namespace app\controllers;

use app\models\training\CredCategory;
use Yii;
use app\models\member\Member;
use yii\web\Controller;
use yii\helpers\Json;
use yii\data\ActiveDataProvider;

class MemberCredentialsController extends Controller
{
	
	public function actionSummaryJson($id)
	{
		$member = Member::findOne($id);

        $recurProvider = new ActiveDataProvider([
            'query' => $member->getCredentials(CredCategory::CATG_RECURRING),
            'sort' => ['defaultOrder' => ['credential_id' => SORT_ASC]],
        ]);
        $nonrecurProvider = new ActiveDataProvider([
            'query' => $member->getCredentials(CredCategory::CATG_NONRECUR),
            'sort' => ['defaultOrder' => ['credential_id' => SORT_ASC]],
        ]);
        $medtestsProvider = new ActiveDataProvider([
            'query' => $member->getCredentials(CredCategory::CATG_MEDTESTS),
            'sort' => ['defaultOrder' => ['credential_id' => SORT_ASC]],
        ]);
        $coreProvider = new ActiveDataProvider([
            'query' => $member->getCredentials(CredCategory::CATG_CORE),
            'sort' => ['defaultOrder' => ['complete_dt' => SORT_DESC]],
        ]);

        echo Json::encode($this->renderAjax('_credentials', [
                'member' => $member,
                'recurProvider' => $recurProvider,
                'nonrecurProvider' => $nonrecurProvider,
                'medtestsProvider' => $medtestsProvider,
                'coreProvider' => $coreProvider,
        ]));
	}
	
	
}