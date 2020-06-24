<?php

namespace app\controllers;


use Exception;
use Yii;
use app\models\accounting\ResponsibleEmployer;
use app\controllers\receipt\MultiMemberController;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptOther;
use app\models\contractor\Contractor;
use yii\web\Response;

class ReceiptOtherController extends MultiMemberController
{

    /**
     * @param $lob_cd
     * @param string $id
     * @return string|Response
     * @throws Exception
     */
    public function actionCreate($lob_cd, $id = Contractor::CONTRACTOR_OTHER_PAYOR)
    {
        $model = new ReceiptOther([
            'responsible' => new ResponsibleEmployer(['license_nbr' => $id]),
            'scenario' => Receipt::SCENARIO_CREATE,
            'lob_cd' => $lob_cd,
        ]);

        if ($model->load(Yii::$app->request->post())) {

            if ($model->validate() && $model->responsible->validate() && $model->save()) {
                $model->responsible->receipt_id = $model->id;
                if ($model->responsible->save(false)) {
                    $session = Yii::$app->session;
                    $session['prebuild'] = 'bypass';
                    return $this->redirect([
                        'itemize',
                        'id' => $model->id,
                        'fee_types' => $model->fee_types,
                    ]);
                }
            }
            $e = array_merge($model->errors, $model->responsible->errors);
            Yii::error("*** ROC010 Receipt save error.  Messages: " . print_r($e, true));
            Yii::$app->session->addFlash('error', 'Problem saving receipt. Check log for details. Code `ROC010`');

        }

        return $this->render('create', [
            'model' => $model,
        ]);

    }




}