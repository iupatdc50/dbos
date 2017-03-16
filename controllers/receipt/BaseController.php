<?php

namespace app\controllers\receipt;

use Yii;
use app\models\accounting\DuesRateFinder;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptSearch;
use app\models\member\Status;
use yii\base\InvalidCallException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\base\yii\base;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;
use app\models\accounting\BaseAllocation;
use app\models\accounting\ReceiptAllocSumm;
use app\models\member;
use app\modules\admin\models\FeeType;


/**
 * BaseController implements the CRUD actions for Receipt model.
 */
class BaseController extends Controller
{
	
	private $_dbErrors;
		
	/** @var array Supplemental data providers */
	protected $otherProviders = [];
	
	public $payor_type_filter = null;
			
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        	'access' => [
        		'class' => AccessControl::className(),
        		'only' => ['index', 'view', 'create', 'update', 'balance', 'delete', 'print-preview'],
        		'rules' => [
        			[
        				'allow' => true,
        				'actions' => ['index', 'view'],
        				'roles' => ['browseReceipt'],
        			],
        			[
        				'allow' => true,
        				'actions' => ['create'],
        				'roles' => ['createReceipt'],
        			],
        			[
        				'allow' => true,
        				'actions' => ['update', 'balance'],
        				'roles' => ['updateReceipt'],
        			],
        			[
        				'allow' => true,
        				'actions' => ['print-preview'],
        				'roles' => ['reportAccounting'],
        			],
        			[
        				'allow' => true,
        				'actions' => ['delete'],
        				'roles' => ['deleteReceipt'],
        			],
        		],
        	],
        			
        ];
    }
    
    /**
     * Lists all Receipt models.
     * @return mixed
     */
    public function actionIndex()
    {
    	return $this->redirect("/accounting");
    }

    public function actionBalance($id)
    {
    	$model = $this->findModel($id);
    	if ($model->load(Yii::$app->request->post())) {
    		if($model->save()) 
    			return $this->goBack();
    		throw new \yii\base\Exception('Problem with post.  Errors: ' . print_r($model->errors, true)); 
    	}		
    	return $this->renderAjax('/receipt/balance', compact('model'));
    }

    /**
     * Updates an existing Receipt model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    
    public function actionPost($id)
    {
    	$model = $this->findModel($id);

    	$allocs = $model->duesAllocations;
    	$this->_dbErrors = [];
    	/* @var $alloc DuesAllocation */
    	foreach ($allocs as $alloc) {
    		if ($this->retainAlloc($alloc)) {
	    		$member = $alloc->member;
	    		$alloc->duesRateFinder = new DuesRateFinder(
	    				$member->currentStatus->lob_cd,
	    				$member->currentClass->rate_class
	    		);
	    		$alloc->months = $alloc->calcMonths();
	    		$alloc->paid_thru_dt = $alloc->calcPaidThru($alloc->months);
	    		if (!$alloc->save()) {
	    			$this->_dbErrors = array_merge($this->_dbErrors, $alloc->errors);
	    		} else {
	    			$member->dues_paid_thru_dt = $alloc->paid_thru_dt;
	    			if ($member->save()) {
	    				if ($member->isInApplication() && ($member->currentApf->balance == 0.00) && ($alloc->estimateOwed() == 0.00)) {
	    					$member->addStatus(new Status(['effective_dt' => $model->received_dt, 'member_status' => Status::ACTIVE, 'reason' => Status::REASON_APF]));
	    					$member->init_dt = $model->received_dt;
	    					if (!$member->save())
	    						$this->_dbErrors = array_merge($this->_dbErrors, $member->errors);
	    				/*
	    				} elseif ($member->currentStatus->member_status == Status::SUSPENDED) {
	    					$member->addStatus(new Status(['effective_dt' => $model->received_dt, 'member_status' => Status::ACTIVE, 'reason' => Status::REASON_DUES]));
	    					if (!$member->save())
	    						$this->_dbErrors = array_merge($this->_dbErrors, $member->errors);
	    				 */
	    				}
	    			} else {
	    				$this->_dbErrors = array_merge($this->_dbErrors, $member->errors);
	    			} 
	    		}
    		}
    	}
    	
    	$allocs = $model->assessmentAllocations;
    	/* @var $alloc AssessmentAllocation */
    	foreach ($allocs as $alloc) {
    		if ($this->retainAlloc($alloc)) {
	    		if ($alloc->applyToAssessment()) {
	    			if (!$alloc->save()) {
	    				$this->_dbErrors = array_merge($this->_dbErrors, $alloc->errors);
	    			}
	    		}
	    		if (($alloc->fee_type == FeeType::TYPE_CC) || ($alloc->fee_type == FeeType::TYPE_REINST)) {
	    			$status = new Status(['effective_dt' => $model->received_dt]);
	    			if ($alloc->fee_type == FeeType::TYPE_CC) {
	    				$status->member_status = Status::INACTIVE;
	    				$status->reason = isset($alloc->allocatedMember->otherLocal) ? Status::REASON_CCG . $alloc->allocatedMember->otherLocal->other_local : 'CCG';
	    			} else { // assume FeeType::TYPE_REINST
	    				$status->member_status = Status::ACTIVE;
	    				$status->reason = Status::REASON_REINST;
	    			}
	    			$member = $alloc->member;
	    			if (!$member->addStatus($status))
	    				$this->_dbErrors = array_merge($this->_dbErrors, $status->errors);;
	    		}	 
    		}
    	}   
    	
    	if (!empty($this->_dbErrors))
    		throw new \yii\base\ErrorException('Problem with post.  Errors: ' . print_r($this->_dbErrors, true));
    	Yii::$app->session->setFlash('success', "Receipt successfully posted");
    	return $this->redirect(['view', 'id' => $model->id]);
    	
    }

    /**
     * Deletes an existing Receipt model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    public function actionPrintPreview($id)
    {
    	$this->layout = 'extreport';
    	$model = $this->findModel($id);
		$query = ReceiptAllocSumm::find()->where(['receipt_id' => $id])->orderBy('descrip');
		$allocProvider = new ActiveDataProvider(['query' => $query, 'sort' => false]);

		return $this->render('/receipt/print-preview', compact('model', 'allocProvider'));
    }
    
    /**
     * Finds the Receipt model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Receipt the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        return null;
    }
    
    /**
     * Allows GoBack() to return to the sending page instead of the home page
     */
    protected function storeReturnUrl()
    {
    	Yii::$app->user->returnUrl = Yii::$app->request->url;
    }
    
    /**
     * Delete the allocation if the allocated amount is zero
     * 
     * @param BaseAllocation $alloc
     * @return boolean Returns false if a database record deletion is attempted
     */
    protected function retainAlloc(BaseAllocation $alloc)
    {
    	$result = true;
    	if ($alloc->allocation_amt == 0.00) {
    		if (!$alloc->delete()) {
    			$this->_dbErrors = array_merge($this->_dbErrors, $alloc->errors);
    		}
    		$result = false;
    	} 
    	return $result;
    }
    
    
    
}
