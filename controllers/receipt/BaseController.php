<?php

namespace app\controllers\receipt;

use Yii;
use app\models\accounting\DuesRateFinder;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptSearch;
use app\models\member\Member;
use app\models\member\Status;
use app\models\member\Standing;
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
use app\modules\admin\models\FeeType;
use app\helpers\ClassHelper;
use app\models\accounting\DuesAllocation;


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
    
    /**
     * Posts the staged receipt
     * 
     * Assessment allocations are applied first because APF status is checked when dues are applied
     * 
     * @param integer $id
     * @throws \yii\base\ErrorException
     * @return \yii\web\Response
     */
    public function actionPost($id)
    {
    	$model = $this->findModel($id);
    	$this->_dbErrors = [];
    	 
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
	    			$member = $alloc->member;
	    			$status = $this->prepareStatus($member, $model->received_dt);
	    			if ($alloc->fee_type == FeeType::TYPE_CC) {
	    				$status->member_status = Status::INACTIVE;
	    				$status->reason = isset($alloc->allocatedMember->otherLocal) ? Status::REASON_CCG . $alloc->allocatedMember->otherLocal->other_local : 'CCG';
	    			} else { // assume FeeType::TYPE_REINST
	    				$status->member_status = Status::ACTIVE;
	    				$status->reason = Status::REASON_REINST;
	    			}
	    			if (!$member->addStatus($status))
	    				$this->_dbErrors = array_merge($this->_dbErrors, $status->errors);;
	    		}	 
    		}
    	}   
    	
    	$allocs = $model->duesAllocations;
    	/* @var $alloc DuesAllocation */
    	foreach ($allocs as $alloc) {
    		if ($this->retainAlloc($alloc)) {
	    		$member = $alloc->member;
	    		$alloc->duesRateFinder = new DuesRateFinder(
	    				$member->currentStatus->lob_cd,
	    				$member->currentClass->rate_class
	    		);
	    		$standing = new Standing(['member' => $member]);
	    		$alloc->months = $alloc->calcMonths() + $standing->getDiscountedMonths();
	    		$alloc->paid_thru_dt = $alloc->calcPaidThru($alloc->months);
	    		if (!$alloc->save()) {
	    			$this->_dbErrors = array_merge($this->_dbErrors, $alloc->errors);
	    		} else {
	    			$member->dues_paid_thru_dt = $alloc->paid_thru_dt;
	    			if ($member->save()) {
	    				if ($member->isInApplication() && ($member->currentApf->balance == 0.00) && ($alloc->estimateOwed() == 0.00)) {
	    					$status = $this->prepareStatus($member, $model->received_dt);
	    					$status->member_status = Status::ACTIVE;
	    					$status->reason = Status::REASON_APF;
	    					$member->addStatus($status);
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
        $model = $this->findModel($id);
        $errors = [];
        foreach($model->members as $alloc_memb) {
        	foreach($alloc_memb->allocations as $alloc) {
        		
        		if ($alloc->fee_type == FeeType::TYPE_DUES) {
        			$dues_alloc = ClassHelper::cast(DuesAllocation::className(), $alloc);
        			// fire event triggers associated with allocations
        			$dues_alloc->delete();
        		}
        	}
        }
        if (!empty($errors))
        	throw new \yii\base\ErrorException('Problem with post.  Errors: ' . print_r($errors, true));
        
        $model->delete();
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
    
    /**
     * Look for existing status to overlay to avoid date conflicts
     * 
     * @param Member $member
     * @param string $date
     * @return \yii\db\static|\app\models\member\Status
     */
    protected function prepareStatus(Member $member, $date)
    {
        if (($status = Status::findOne(['member_id' => $member->member_id, 'effective_dt' => $date])) !== null) 
            return $status;
    	;
    	return new Status(['effective_dt' => $date]);
    }
    
}
