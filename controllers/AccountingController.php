<?php

namespace app\controllers;

use Yii;
use \yii\web\Controller;
use yii\web\Response;
use yii\helpers\Json;
use app\models\accounting\Receipt;
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
        
        return $this->render('index', [
        ]);
    }
    
	public function actionReinstate() {
		$modelReceipt = new ReceiptMember;
		$modelMember = new AllocatedMember();
		$modelsAllocation = [new BaseAllocation];
		
		/* Begin mockup */
		
		$modelReceipt->payor_nm = 'Agno, Shawn W.W.U.';
		$modelReceipt->received_amt = 224.60;
		
		$modelsAllocation[0]->fee_type = 'DU';
		$modelsAllocation[0]->allocation_amt = 174.60;
		$modelsAllocation[] = new BaseAllocation();
		$modelsAllocation[1]->fee_type = 'RN';
		$modelsAllocation[1]->allocation_amt = 50.00;
		
		/* End Mockup */
		
		
		
		return $this->renderAjax('reinstate', [
				'modelReceipt' => $modelReceipt,
				'modelMember' => $modelMember,
				'modelsAllocation' => (empty($modelsAllocation)) ? [new BaseAllocation] : $modelsAllocation,
		]);
		
	}
}