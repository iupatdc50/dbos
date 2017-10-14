<?php

namespace app\controllers;

use Yii;
use \yii\web\Controller;
use yii\web\Response;
use yii\helpers\Json;
use app\models\accounting\CreateReceiptForm;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptSearch;
use app\models\member\Member;

class AccountingController extends Controller
{
	
// 	public $layout = 'accounting';
	
    /**
     * @return mixed
     */
    public function actionIndex($mine_only = null)
    {
        $searchModel = new ReceiptSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $mine_only);
        $payorPicklist = $searchModel->payorOptions;
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        	'payorPicklist' => $payorPicklist,
        	'mine_only' => $mine_only,
        ]);
    }
    
    public function actionCreateReceipt()
    {
//    	$model = new Receipt(['scenario' => Receipt::SCENARIO_CONFIG]);
		$model = new CreateReceiptForm();
    	if ($model->load(Yii::$app->request->post())) {
    		if ($model->validate(['lob_cd'])) {
	    		if ($model->payor_type == Receipt::PAYOR_CONTRACTOR) {
	    			return $this->redirect(['/receipt-contractor/create', 'lob_cd' => $model->lob_cd, 'id' => $model->license_nbr]);
	    		} elseif ($model->payor_type == Receipt::PAYOR_MEMBER) {
	    			$member = Member::findOne($model->member_id);
	    			if (!isset($member->currentStatus))
	    				throw new \InvalidArgumentException('No status entry for member: ' . $member->member_id);
	    			return $this->redirect([
	    					'/receipt-member/create', 
	    					'lob_cd' => $member->currentStatus->lob_cd, 
	    					'id' => $model->member_id,
	    			]);
	    		} else {
	    			throw new HttpException('Feature not supported');
	    		}
    		}
    	}
    	$payorOptions = Receipt::getPayorOptions();
    	return $this->renderAjax('create-receipt', compact('model', 'payorOptions'));
    }
    
    /**
     * Deletes an existing Receipt model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findReceiptModel($id)->delete();
        return $this->redirect(['index']);
    }
    
    protected function findReceiptModel($id)
    {
		$receipt = Receipt::findOne($id);
        if (!$receipt)
        	throw new \InvalidArgumentException('Attemtping to access a non-existent receipt: ' . $id);
        return $receipt;
    }
    
}