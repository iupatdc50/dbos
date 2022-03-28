<?php

namespace app\controllers\receipt;

use app\models\accounting\StatusManagerAssessment;
use app\models\accounting\StatusManagerDues;
use Exception;
use Throwable;
use Yii;
use app\models\accounting\Receipt;
use app\models\member\Member;
use app\models\member\Standing;
use app\models\member\OverageHistory;
use yii\db\Exception as DbException;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use app\models\accounting\BaseAllocation;
use app\models\accounting\ReceiptAllocSumm;
use app\components\utilities\OpDate;
use yii\web\NotFoundHttpException;
use yii\web\Response;


/**
 * BaseController implements the CRUD actions for Receipt model.
 *
 * @property OpDate $today
 */
class BaseController extends Controller
{
	
	private $_dbErrors;
		
	/** @var array view data providers */
	protected $config = [];
	
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
     * @return Response
     */
    public function actionIndex()
    {
    	return $this->redirect("/accounting");
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionBalancesJson($id)
    {
        $model = $this->findModel($id);
        /** @noinspection PhpWrongStringConcatenationInspection */
        $running = $model->totalAllocation + $model->unallocated_amt + $model->helper_dues;
        return $this->asJson([
                'balance' => number_format($model->outOfBalance, 2),
                'running' => number_format($running, 2),
        ]);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws DbException
     * @throws NotFoundHttpException
     */
    public function actionBalance($id)
    {
    	$model = $this->findModel($id);
    	if ($model->load(Yii::$app->request->post())) {
    		if($model->save()) {
    			return $this->goBack();
    		}
    		throw new DbException('Problem with post.  Errors: ' . print_r($model->errors, true));
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
     * @return Response
     * @throws DbException
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionPost($id)
    {
    	$model = $this->findModel($id);
    	
    	// Can't post an out of balance receipt
    	if($model->outOfBalance != 0.00)
    		return $this->goBack();
    	
    	$this->_dbErrors = [];
    	 
        $allocs = $model->assessmentAllocations;
        foreach ($allocs as $alloc) {
    		if ($this->retainAlloc($alloc)) {
                $manager = new StatusManagerAssessment();
                $this->_dbErrors = array_merge($this->_dbErrors, $manager->applyAssessment($alloc));
            }
    	}   
    	
    	$allocs = $model->duesAllocations;
        foreach ($allocs as $alloc) {
    		if ($this->retainAlloc($alloc)) {
	    		$manager = $this->prepareManager($alloc);
	    		$this->_dbErrors = array_merge($this->_dbErrors, $manager->applyDues($alloc));
	    		if($alloc->member->overage <> 0.00) {
	    		    $history = $this->prepareOverageHistory($alloc->member);
                    $history->receipt_id = $alloc->allocatedMember->receipt_id;
                    $history->overage = $alloc->member->overage;
	    		    $alloc->member->addOverageHistory($history);
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

        $model->cleanup();
    	$session->setFlash('success', "Receipt successfully posted");
    	return $this->redirect(['view', 'id' => $model->id]);
    	
    }

    /**
     * @param $id
     * @return Response | string
     * @throws DbException
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionUpdate($id)
    {
        $this->storeReturnUrl();
        $modelReceipt = $this->findModel($id);

        if ($modelReceipt->load(Yii::$app->request->post())) {
            // ensure mark receipt dirty => assume dependencies updated
            $modelReceipt->dependenciesUpdated();
            if($modelReceipt->save()) {
                if ($modelReceipt->outOfBalance == 0.00) {

                    $this->_dbErrors = [];

                    $allocs = $modelReceipt->assessmentAllocations;
                    foreach ($allocs as $alloc) {
                        $undo = $alloc->undoAllocation;
                        // If a new allocation or amount changed or if assessment ID was reset to null (alloc was reassigned)
                        if ((!isset($undo)) ||
                                    ($alloc->allocation_amt != $undo->allocation_amt) ||
                                    ($alloc->assessment_id === null)) {
                            $manager = new StatusManagerAssessment();
                            $this->_dbErrors = array_merge($this->_dbErrors, $manager->applyAssessment($alloc));
                        }
                    }

                    $allocs = $modelReceipt->duesAllocations;
                    foreach ($allocs as $alloc) {
                        if ($alloc->months == null) {
                            $manager = $this->prepareManager($alloc);
                            $this->_dbErrors = array_merge($this->_dbErrors, $manager->applyDues($alloc));
                        }
                    }

                    if (empty($this->_dbErrors)) {
                        $modelReceipt->cleanup();
                        Yii::$app->session->setFlash('success', "Receipt successfully updated");
                        return $this->redirect(['view', 'id' => $modelReceipt->id]);
                    }

                    Yii::$app->session->setFlash('error', "Problem with save.  Check log for details. Code `BC020`");
                    Yii::error("*** BC020: Problem with Receipt update.  Errors: " . print_r($this->_dbErrors, true));

                }
            }
        }

        $modelReceipt->makeUndo($id);
        $this->config['modelReceipt'] = $modelReceipt;

        /** @noinspection MissedViewInspection */
        return $this->render('update', $this->config);

    }

    /**
     * Voids an existing Receipt model.
     *
     * @param integer $id
     * @return Response
     * @throws Exception
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionVoid($id)
    {
        $model = $this->findModel($id);

        $result = $model->void(Yii::$app->user->identity->username);
        
        if (empty($result))
        	Yii::$app->session->setFlash('success', "Receipt successfully voided");
        else {
			Yii::$app->session->addFlash('error', 'Could not complete receipt void.  Check log for details. Code `BC020`');
			Yii::error("*** BC020 Receipt void error(s).  Errors: " . print_r($result, true) . " Receipt: " . print_r($model, true));
        }

        return $this->redirect(['view', 'id' => $model->id]);
        
    }

    /**
     * Backs out of a create or update action.  If the receipt is held in the `Undo` tables, the held receipt is restored.
     *
     * @param integer $id
     * @return Response
     * @throws Exception
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionCancelCreate($id)
    {
    	$model = $this->findModel($id);
    	$this->_dbErrors = [];
    	
    	foreach($model->members as $alloc_memb)
            // Calls removeAllocations() manually to merge errors with base action
            $this->_dbErrors = array_merge($this->_dbErrors, $alloc_memb->removeAllocations());
    	
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

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionCancelUpdate($id)
    {
        $model = $this->findModel($id);
        try {
            $model->cancelUpdate($id);
        } catch (DbException $e) {
            Yii::$app->session->addFlash('error', 'Could not complete cancel action.  Check log for details. Code `BC040`');
            Yii::error("*** BC040 Receipt cancellation error(s).  Errors: " . print_r($e->errorInfo, true) . " Receipt: " . print_r($model, true));
            return $this->goBack();
        }
        Yii::$app->session->addFlash('success', 'Receipt update cancelled');

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
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
     * Finds the Receipt model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Receipt the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function findModel($id)
    {
        if (($model = Receipt::findOne($id)) == null) {
            Yii::error("*** BC050: Could not find receipt `$id`");
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $model;
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
     * @throws Exception
     * @throws StaleObjectException
     * @throws Throwable
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
     * Look for existing overage history to overlay
     *
     * @param Member $member
     * @return OverageHistory
     */
    protected function prepareOverageHistory(Member $member)
    {
        $history = OverageHistory::findOne(['member_id' => $member->member_id, 'dues_paid_thru_dt' => $member->dues_paid_thru_dt]);
        if (!isset($history))
            $history = new OverageHistory([
                'member_id' => $member->member_id,
                'dues_paid_thru_dt' => $member->dues_paid_thru_dt,
            ]);
        return $history;
    }
    
    /**
     * Override this function when testing with fixed date
     *
     * @return OpDate
     */
    protected function getToday()
    {
    	return new OpDate();
    }

    /**
     * @param $alloc
     * @return StatusManagerDues
     */
    protected function prepareManager($alloc)
    {
        $member = $alloc->member;
        $standing = new Standing(['member' => $member]);
        return new StatusManagerDues(['standing' => $standing]);

    }
    
}
