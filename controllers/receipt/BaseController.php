<?php

namespace app\controllers\receipt;

use Yii;
use app\models\accounting\DuesRateFinder;
use app\models\accounting\Receipt;
use app\models\member\Member;
use app\models\member\Status;
use app\models\member\Standing;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;
use app\models\accounting\BaseAllocation;
use app\models\accounting\ReceiptAllocSumm;
use app\modules\admin\models\FeeType;
use app\helpers\ClassHelper;
use app\models\accounting\DuesAllocation;
use app\components\utilities\OpDate;
use app\helpers\OptionHelper;
use app\models\accounting\AllocatedMember;


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
        		'only' => ['index', 'view', 'create', 'update', 'balance', 'void', 'print-preview'],
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
        				'actions' => ['void'],
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
    
    public function actionBalancesJson($id)
    {
        $model = $this->findModel($id);
        /** @noinspection PhpWrongStringConcatenationInspection */
        $running = $model->totalAllocation + $model->unallocated_amt + $model->helper_dues;
        echo Json::encode([
                'balance' => number_format($model->outOfBalance, 2),
                'running' => number_format($running, 2),
        ]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionBalance($id)
    {
    	$model = $this->findModel($id);
    	if ($model->load(Yii::$app->request->post())) {
    		if($model->save()) {
    			return $this->goBack();
    		}
    		throw new \yii\db\Exception('Problem with post.  Errors: ' . print_r($model->errors, true));
    	}
        /** @noinspection MissedViewInspection */
        return $this->renderAjax('/receipt/balance', compact('model'));
    }

    /**
     * Posts the staged receipt
     *
     * Assessment allocations are applied first because APF status is checked when dues are applied
     *
     * @param integer $id Receipt ID
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionPost($id)
    {
    	$model = $this->findModel($id);
    	
    	// Can't post an out of balance receipt
    	if($model->outOfBalance != 0.00)
    		return $this->goBack();
    	
    	$this->_dbErrors = [];
    	 
        $allocs = $model->assessmentAllocations;
    	/* @var $alloc \app\models\accounting\AssessmentAllocation */
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
                    $status->alloc_id = $alloc->id;
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
	    				isset($member->currentClass) ? $member->currentClass->rate_class : 'R'
	    		);
	    		$standing = new Standing(['member' => $member]);
	    		$alloc->months = $alloc->calcMonths($member->overage) + $standing->getDiscountedMonths();
	    		$alloc->paid_thru_dt = $alloc->calcPaidThru($alloc->months);
	    		if (!$alloc->save()) {
	    			$this->_dbErrors = array_merge($this->_dbErrors, $alloc->errors);
	    		} else {
	    			$member->dues_paid_thru_dt = $alloc->paid_thru_dt;
	    			$member->overage = $alloc->unalloc_remainder;
	    			if ($member->save()) {
	    				if ($member->isInApplication() && ($member->currentApf->balance == 0.00) && ($alloc->estimateOwed() == 0.00)) {
	    					$status = $this->prepareStatus($member, $model->received_dt);
	    					$status->member_status = Status::ACTIVE;
	    					$status->reason = Status::REASON_APF;
                            $status->alloc_id = $alloc->id;
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

    	$session = Yii::$app->session;
    	
    	if (!empty($this->_dbErrors)) {
    	    $session->setFlash('error', "Problem with post.  Check log for details. Code `BC010`");
    	    Yii::error("*** BC010: Problem with Receipt post.  Errors: " . print_r($this->_dbErrors, true));
    	    return $this->goBack();
        }

        if (isset($session['prebuild']))
            unset($session['prebuild']);

    	$session->setFlash('success', "Receipt successfully posted");
    	return $this->redirect(['view', 'id' => $model->id]);
    	
    }

    /**
     * @param $id
     * @return string
     */
    public function actionUpdate($id)
    {
        $this->storeReturnUrl();
        $modelReceipt = $this->findModel($id);

        if ($modelReceipt->load(Yii::$app->request->post()) && $modelReceipt->save()) {
            if ($modelReceipt->outOfBalance == 0.00) {
                Yii::$app->session->setFlash('success', "Receipt successfully updated");
                return $this->redirect(['view', 'id' => $modelReceipt->id]);
            }
        }

        return $this->render('/receipt/update', [
            'modelReceipt' => $modelReceipt,
            'controller' => $this->id,
        ]);

    }

    /**
     * Voids an existing Receipt model.
     *
     * @param integer $id
     * @return mixed
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function actionVoid($id)
    {
        $model = $this->findModel($id);
        $this->_dbErrors = [];
        
        foreach($model->members as $alloc_memb) {
        	$this->removeDuesAllocations($alloc_memb);
        	if (!$alloc_memb->delete())
        		$this->_dbErrors = array_merge($this->_dbErrors, $alloc_memb->errors);
        }

        $model->received_amt = 0.00;
        $model->unallocated_amt = 0.00;
        $model->helper_dues = null;
        /** @noinspection PhpUndefinedFieldInspection */
        $model->remarks = 'Voided by: ' . Yii::$app->user->identity->username;
        $model->void = OptionHelper::TF_TRUE;
        
        if (!$model->save())
        	$this->_dbErrors = array_merge($this->_dbErrors, $model->errors);
        
        if (empty($this->_dbErrors))
        	Yii::$app->session->setFlash('success', "Receipt successfully voided");
        else {
			Yii::$app->session->addFlash('error', 'Could not complete receipt void.  Check log for details. Code `BC020`');
			Yii::error("*** BC020 Receipt void error(s).  Errors: " . print_r($this->_dbErrors, true) . " Receipt: " . print_r($model, true));
        }

        return $this->redirect(['view', 'id' => $model->id]);
        
    }

    /**
     * Backs out of a create or update action.  If the receipt is held in the Undo tables, the held receipt is restored.
     *
     * @param integer $id
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function actionCancel($id)
    {
    	$model = $this->findModel($id);
    	$this->_dbErrors = [];
    	
    	foreach($model->members as $alloc_memb) 
    		$this->removeDuesAllocations($alloc_memb);
    	
    	if (!$model->delete())
    		$this->_dbErrors = array_merge($this->_dbErrors, $model->errors);

    	$session = Yii::$app->session;
    	
    	if (!empty($this->_dbErrors)) {
    		$session->addFlash('error', 'Could not complete cancel action.  Check log for details. Code `BC030`');
    		Yii::error("*** BC030 Receipt cancellation error(s).  Errors: " . print_r($this->_dbErrors, true) . " Receipt: " . print_r($model, true));
    		return $this->goBack();
    	}

    	if (isset($session['prebuild']))
            unset($session['prebuild']);
    	 
    	return $this->redirect(['index']);
    }

    public function actionPrintPreview($id)
    {
    	$this->layout = 'noheadreport';
    	$model = $this->findModel($id);
		$query = ReceiptAllocSumm::find()->where(['receipt_id' => $id])->orderBy('descrip');
		$allocProvider = new ActiveDataProvider(['query' => $query, 'sort' => false]);

        /** @noinspection MissedViewInspection */
        return $this->render('/receipt/print-preview', compact('model', 'allocProvider'));
    }
    
    /**
     * Signature for finding the Receipt model based on its primary key value.
     * Must be overriden.
     * @param integer $id
     * @return Receipt the loaded model
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
     * @return boolean Returns false if a database record deletion is unsuccessful
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
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
    
    protected function removeDuesAllocations(AllocatedMember $alloc_memb)
    {
    	foreach($alloc_memb->allocations as $alloc) {
    	
    		if ($alloc->fee_type == FeeType::TYPE_DUES) {
    			$dues_alloc = ClassHelper::cast(DuesAllocation::className(), $alloc);
    			// fire event triggers associated with allocations
    			if (!$dues_alloc->delete())
    				$this->_dbErrors = array_merge($this->_dbErrors, $dues_alloc->errors);
    		}
    	}
    	
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
    
    /**
     * Override this function when testing with fixed date
     *
     * @return \app\components\utilities\OpDate
     */
    protected function getToday()
    {
    	return new OpDate();
    }
    
    protected function initCreate($model)
    {
    	if (!isset($model->received_dt)) {
    		$model->received_dt = $this->today->getMySqlDate();
    		if (!isset($model->acct_month))
    			$model->acct_month = $this->today->getYearMonth();
    	}
    }
    
}
