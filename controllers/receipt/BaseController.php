<?php

namespace app\controllers\receipt;

use Yii;
use app\models\accounting\DuesRateFinder;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptSearch;
use app\models\accounting\AllocatedMemberSearch;
use yii\base\InvalidCallException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\base\yii\base;
use app\models\accounting\app\models\accounting;


/**
 * BaseController implements the CRUD actions for Receipt model.
 */
class BaseController extends Controller
{
		
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

    /**
     * Displays a single Receipt model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
    	$model = $this->findModel($id);
    	    	
    	$searchMemb = new AllocatedMemberSearch(['receipt_id' => $id]);
    	$membProvider = $searchMemb->search(Yii::$app->request->queryParams);

    	return $this->render('/receipt/view', compact('model', 'membProvider', 'searchMemb'));
    }
    
    public function actionBalance($id, array $fee_types)
    {
    	$model = $this->findModel($id);
    	$model->fee_types = $fee_types;
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
    	$save_errors = [];
    	/* @var $alloc DuesAllocation */
    	foreach ($allocs as $alloc) {
    		$member = $alloc->member;
    		$alloc->duesRateFinder = new DuesRateFinder(
    				$member->currentStatus->lob_cd,
    				$member->currentClass->rate_class
    		);
    		$alloc->months = $alloc->calcMonths();
    		$alloc->paid_thru_dt = $alloc->calcPaidThru($alloc->months);
    		if (!$alloc->save()) {
    			$save_errors = array_merge($save_errors, $alloc->errors);
    		} else {
    			$member->dues_paid_thru_dt = $alloc->paid_thru_dt;
    			if (!$member->save())
    				$save_errors = array_merge($save_errors, $member->errors);;
    		}
    	}
    	
    	$allocs = $model->assessmentAllocations;
    	/* @var $alloc AssessmentAllocation */
    	foreach ($allocs as $alloc) {
    		if ($alloc->applyToAssessment()) {
    			if (!$alloc->save()) {
    				$save_errors = array_merge($save_errors, $alloc->errors);
    			}
    		}	 
    	}   
    	
    	
    	if (!empty($save_errors))
    		throw new \yii\base\ErrorException('Problem with post.  Errors: ' . print_r($save_errors, true));
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
}
