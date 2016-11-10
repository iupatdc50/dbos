<?php

namespace app\controllers;

use Yii;
use \yii\web\Controller;
use yii\web\Response;
use yii\helpers\Json;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptSearch;
use app\models\accounting\ReceiptMember;
use app\models\accounting\AllocatedMember;
use app\models\accounting\BaseAllocation;
use app\models\accounting\AssessmentAllocation;
use app\models\accounting\DuesAllocation;
use \app\models\member\Member;
use \app\models\accounting\DuesRateFinder;
use yii\helpers\ArrayHelper;

class AccountingController extends Controller
{
	
 	public $layout = 'accounting';
	
    /**
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ReceiptSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $payorPicklist = $searchModel->payorOptions;
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        	'payorPicklist' => $payorPicklist,
        ]);
    }
    
    public function actionCreateReceipt()
    {
    	$model = new Receipt(['scenario' => Receipt::SCENARIO_CONFIG]);
    	if ($model->load(Yii::$app->request->post())) {
    		if ($model->validate(['lob_cd'])) {
	    		if ($model->payor_type == Receipt::PAYOR_CONTRACTOR) {
	    			return $this->redirect(['/receipt-contractor/create', 'lob_cd' => $model->lob_cd]);
	    		} elseif ($model->payor_type == Receipt::PAYOR_MEMBER) {
	    			return $this->redirect(['/receipt-member/create', 'lob_cd' => $model->lob_cd]);
	    		} else {
	    			throw new HttpException('Feature not supported');
	    		}
    		}
    	}
    	return $this->renderAjax('create-receipt', compact('model'));
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