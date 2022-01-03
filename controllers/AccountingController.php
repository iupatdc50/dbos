<?php

namespace app\controllers;

use app\models\member\SubscriptionEventSearch;
use Exception;
use InvalidArgumentException;
use kartik\widgets\ActiveForm;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\web\Controller;
use app\models\accounting\CreateReceiptForm;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptSearch;
use app\models\member\Member;
use yii\web\Response;

class AccountingController extends Controller
{
	
// 	public $layout = 'accounting';

    /**
     * @param null $mine_only
     * @return string
     */
    public function actionIndex($mine_only = null)
    {
        Yii::$app->user->returnUrl = Yii::$app->request->url;

        $receiptSearchModel = new ReceiptSearch();
        $receiptProvider = $receiptSearchModel->search(Yii::$app->request->queryParams, $mine_only);

        $eventSearchModel = new SubscriptionEventSearch();
        $eventProvider = $eventSearchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'receiptSearchModel' => $receiptSearchModel,
            'receiptProvider' => $receiptProvider,
        	'payorPicklist' => ReceiptSearch::getPayorOptions(),
        	'mine_only' => $mine_only,
            'eventSearchModel' => $eventSearchModel,
            'eventProvider' => $eventProvider,
            'statusPicklist' => SubscriptionEventSearch::getStatusOptions(),
        ]);
    }
    
    public function actionCreateReceipt()
    {
//    	$model = new Receipt(['scenario' => Receipt::SCENARIO_CONFIG]);
		$model = new CreateReceiptForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = 'json';
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->payor_type == Receipt::PAYOR_CONTRACTOR) {
                return $this->redirect(['/receipt-contractor/create', 'lob_cd' => $model->lob_cd, 'id' => $model->license_nbr]);
            } elseif ($model->payor_type == Receipt::PAYOR_MEMBER) {
                $member = Member::findOne($model->member_id);
                if (!isset($member->currentStatus))
                    throw new InvalidArgumentException('No status entry for member: ' . $member->member_id);
                return $this->redirect([
                    '/receipt-member/create',
                    'lob_cd' => $member->currentStatus->lob_cd,
                    'id' => $model->member_id,
                ]);
            } elseif ($model->payor_type == Receipt::PAYOR_OTHER) {
                return $this->redirect(['/receipt-other/create', 'lob_cd' => $model->other_lob_cd]);
            } else {
                Yii::$app->session->addFlash('error', 'Feature not supported.  Payor type: ' . $model->payor_type);
                return $this->goBack();
            }

    	}
    	$payorOptions = Receipt::getPayorOptions();
    	return $this->renderAjax('create-receipt', compact('model', 'payorOptions'));
    }

    /**
     * Deletes an existing Receipt model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return Response
     * @throws Exception
     * @throws StaleObjectException|Throwable
     */
    public function actionDelete($id)
    {
        $this->findReceiptModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * @param $id
     * @return Receipt
     */
    protected function findReceiptModel($id)
    {
		$receipt = Receipt::findOne($id);
        if (!$receipt)
        	throw new InvalidArgumentException('Attemtping to access a non-existent receipt: ' . $id);
        return $receipt;
    }

}