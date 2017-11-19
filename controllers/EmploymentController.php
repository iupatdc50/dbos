<?php

namespace app\controllers;

use app\controllers\base\SummaryController;
use Yii;
use app\models\member\Member;
use app\models\member\Employment;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * EmploymentController implements the CRUD actions for Employment model.
 */
class EmploymentController extends SummaryController
{
	public $recordClass = 'app\models\member\Employment';
	public $relationAttribute = 'member_id';
	
	/**
	 * Overrides summary controller action to process image attachment
	 * 
	 * (non-PHPdoc)
	 * @see \app\controllers\base\SubmodelController::actionCreate()
	 */
    public function actionCreate($relation_id)
    {
    	/** @var ActiveRecord $model */
        $model = new Employment;
        
        // Loan has its own action
		$model->is_loaned = 'F';
        if ($model->load(Yii::$app->request->post())) {
	        // Prepopulate referencing column
	        $model->member_id = $relation_id;
        	$image = $model->uploadImage();
        	if	($model->save()) {
        		if ($image !== false) {
        			$path = $model->imagePath;
        			$image->saveAs($path);
        		}
        		Yii::$app->session->addFlash('success', "{$this->getBasename()} entry created");
        		return $this->goBack();
        	}
        } 
        return $this->renderAjax('create', compact('model'));

    }
	
	public function actionUpdate($id)
	{
		throw new NotFoundHttpException('Non-supported feature.  Cannot update employment this way.');
	}
	
	public function actionSummaryJson($id)
	{
		$member = Member::findOne($id);
		$employer = isset($member->employer) ? $member->employer->descrip : 'Unemployed';
		$this->viewParams = ['employer' => $employer];
		parent::actionSummaryJson($id);
	}

	public function actionEdit($member_id, $effective_dt)
	{
		$model = $this->findByDate($member_id, $effective_dt);
		
	    $oldPath = $model->imagePath;
        $oldId = $model->doc_id;
        
        if ($model->load(Yii::$app->request->post())) {
        	$image = $model->uploadImage();
        	
        	if($image === false)
        		$model->doc_id = $oldId;
         
        	if	($model->save()) {
            	if ($image !== false && (($oldPath === null) || unlink($oldPath))) {
    				$path = $model->imagePath;
    				$image->saveAs($path);
    			}
    			Yii::$app->session->addFlash('success', "{$this->getBasename()} entry updated");
        		return $this->goBack();
        	}
        } 
        		
        return $this->render('edit', compact('model'));
	}
	
	/**
	 * Replaces the inherited controller actionDelete with a different signature
	 * 
	 * @param string $member_id
	 * @param string $effective_dt
	 * @throws NotFoundHttpException  If the model is not found
	 * @return \yii\web\Response
	 */
	public function actionRemove($member_id, $effective_dt)
	{
		$model = $this->findByDate($member_id, $effective_dt);
		if ($model !== null) {
			
			$removing_current = false;
        	if ($model->end_dt == null) {
        		$removing_current = true;
        		$qualifier = $model->qualifier();
        		$relation_id = $model->$qualifier;
        	}
			
        	$model->delete();

        	if ($removing_current)
        		 Employment::openLatest($member_id);
			
        	Yii::$app->session->addFlash('success', "{$this->getBasename()} entry deleted");
			return $this->goBack();
		}
		throw new NotFoundHttpException('Unable to locate employment record.');
	}
	
	public function actionLoan($relation_id)
	{
		/** @var ActiveRecord $model */
		$model = new $this->recordClass;
		// Prepopulate referencing column
		$model->{$this->relationAttribute} = $relation_id;
		// Assumptions
		$model->employer = $this->findCurrent($relation_id)->employer;
		$model->is_loaned = 'T';
		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			Yii::$app->session->addFlash('success', "{$this->getBasename()} Loan entry created");
			return $this->goBack();
		}
		
		return $this->renderAjax('loan', compact('model'));
	}
	
	public function actionTerminate($relation_id)
	{
		$model = $this->findCurrent($relation_id);
		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			return $this->goBack();
		}
    	$termReasonOptions = Employment::getTermReasonOptions();
		return $this->renderAjax('terminate', compact('model', 'termReasonOptions'));
	}
	
	/**
	 * List builder for employee pickllist.  Builds JSON encoded array:
	 * ['results'] key provides progressive results. If a member_id is provided,
	 * 			   then this key provides the member_id and member's full name
	 *
	 * @param string|array $search Criteria used.
	 * @param string $license_nbr Contractor who employs these members
	 * @param string $member_id Selected member's member_id
	 */
	public function actionEmployeeList($search = null, $employer = null, $member_id = null)
	{
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		$out = ['results' => ['id' => '', 'text' => '']];
		if (!is_null($search)) {
			$condition = (is_null($employer)) ? $search : ['full_nm' => $search, 'employer' => $employer];
			$data = Employment::listEmployees($condition);
			$out['results'] = array_values($data);
		}
		elseif (!is_null($member_id) && ($member_id <> '0')) {
			$out['results'] = ['member_id' => $member_id, 'text' => Member::findOne($member_id)->full_nm];
		}
		return $out;
	}
	
	protected function findCurrent($id)
	{
		return Employment::findCurrentEmployer($id);
	}
	
	protected function findByDate($id, $dt)
	{
		return Employment::findEmployerByDate($id, $dt);
	}
	
}
