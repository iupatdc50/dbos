<?php

namespace app\controllers;

use app\models\accounting\ReceiptMember;
use app\models\member\IdCard;
use Yii;
use app\controllers\base\RootController;
use app\models\member\Member;
use app\models\member\MemberId;
use app\models\member\Address;
use app\models\member\Phone;
use app\models\member\Email;
use app\models\member\Status;
use app\models\member\StatusCode;
use app\models\member\MemberClass;
use app\models\member\Note;
use app\models\member\Document;
use app\models\accounting\DuesRateFinder;
use app\models\member\Standing;
use app\models\member\MemberSearch;
use yii\data\SqlDataProvider;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use app\helpers\OptionHelper;
use app\components\utilities\OpDate;
use yii\data\ActiveDataProvider;
use yii\bootstrap\ActiveForm;


/**
 * MemberController implements the CRUD actions for Member model.
 */
class MemberController extends RootController
{
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
	        	'only' => ['index', 'view', 'create', 'update', 'delete', 'photo', 'photo-clear'],
	            'rules' => [
	                [
	                    'allow' => true,
	                    'actions' => ['index', 'view'],
	                    'roles' => ['browseMember', 'uploadDocs'],
	                ],
	                [
	                    'allow' => true,
	                    'actions' => ['create', 'create-stub'],
	                    'roles' => ['createMember'],
	                ],
	                [
	                    'allow' => true,
	                    'actions' => ['update', 'photo', 'photo-clear'],
	                    'roles' => ['updateDemo'],
	                ],
	                [
	                    'allow' => true,
	                    'actions' => ['delete'],
	                    'roles' => ['deleteMember'],
	                ],
	            ],
	        ],
        		
        ];
    }

    /**
     * Lists all Member models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MemberSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $statusPicklist = ArrayHelper::map(StatusCode::find()->orderBy('member_status_cd')->all(), 'member_status_cd', 'descrip');
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        	'statusPicklist' => $statusPicklist,
        ]);
    }

    /**
     * Displays a single Member model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionView($id)
    {
    	parent::actionView($id);
    	
    	$view = Yii::$app->user->can('browseMember') ? 'view' : 'viewext';
    	$model = $this->findModel($id);
    	$params = [];
    	$params['model'] = $model;    

    	$balance = 'Pending';
    	if (isset($model->currentStatus) && isset($model->currentClass)) {
            $standing = new Standing(['member' => $model]);
            $balance = $standing->totalAssessmentBalance - $model->overage;
    	    if ($model->currentStatus->member_status != Status::OUTOFSTATE) {
                $rate_finder = new DuesRateFinder($model->currentStatus->lob_cd, $model->currentClass->rate_class);
                $balance += $standing->getDuesBalance($rate_finder);
            }
            $balance = number_format($balance, 2);
    	}
    	$params['balance'] = $balance;
    	
    	if (Yii::$app->user->can('browseMember')) {
    		$params['noteModel'] = $this->createNote($model);
    	} else {
    		// Specific for doc uploader view
	    	$query = Document::find()
	    						->where(['member_id' => $id])
	    						->orderBy('doc_type asc');
	    	
	    	$params['docProvider'] = new ActiveDataProvider([
	    			'query' => $query,
	    			'sort' => false,
	    	]);
    	}
    	 

    	return $this->render($view, $params);
    }

    /**
     * Creates a new Member model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
    	$this->storeReturnUrl();
    	
    	$idGenerator = new MemberId();
        $model = new Member(['idGenerator' => $idGenerator, 'scenario' => Member::SCENARIO_CREATE]);
        $modelAddress = new Address(['set_as_default' => true]);
        $modelPhone = new Phone(['set_as_default' => true]);
        $modelEmail = new Email;
        $modelStatus = new Status;
        $modelClass = new MemberClass(['scenario' => MemberClass::SCENARIO_CREATE]);
        
        // New member defaults
        $model->exempt_apf = false;
        
        if ($model->load(Yii::$app->request->post())
        		&& $modelAddress->load(Yii::$app->request->post()) 
        		&& $modelPhone->load(Yii::$app->request->post()) 
        		&& $modelEmail->load(Yii::$app->request->post()) 
        		&& $modelStatus->load(Yii::$app->request->post())
        		&& $modelClass->load(Yii::$app->request->post())) {
        	
        	$image = $model->uploadImage();
        	if ($model->validate() && $modelAddress->validate() && $modelPhone->validate() && $modelEmail->validate()) {
        		
        		$model->init_dt = ($model->exempt_apf) ? $model->application_dt : null;
        		 
        		$transaction = \Yii::$app->db->beginTransaction();
        		try {
        			if ($model->save(false)) {
        				if ($image !== false) {
		        			$path = $model->imagePath;
		        			$image->saveAs($path);
		        		}

                        if (isset($modelEmail->email)) {
                            $modelEmail->member = $model;
                            $modelEmail->save(false);
                        }

                        $modelAddress->member_id = $model->member_id;
						$modelPhone->member_id = $model->member_id;        				


        				if ($modelAddress->save(false) && $modelPhone->save(false)) {
							// Assume lob_cd comes from $_POST
							$modelStatus->effective_dt = $model->application_dt;
							$modelStatus->reason = Status::REASON_NEW;
        					if (!$model->addStatus($modelStatus))
        						throw new \Exception('Error when adding Status: ' . print_r($modelStatus->errors, true));
        					// Assume class_id comes from $_POST
        					$modelClass->effective_dt = $model->application_dt;
        					if (!$model->addClass($modelClass))
        						throw new \Exception('Error when adding Member Class: ' . print_r($modelClass->errors, true));
        					if ($model->isInApplication()) {
        						$model->createApfAssessment();
        					}
        					$transaction->commit();
        					Yii::$app->session->setFlash('success', "Member record successfully created");
							return $this->redirect(['view', 'id' => $model->member_id]);
        				}
        			}
        		    $transaction->rollBack();
        		} catch (\Exception $e) {
        			Yii::$app->session->addFlash('error', 'Could not save record. Please report the following to Tech Support: [errno: ' . $e->getCode() . ']');
        			Yii::error('*** Could not add member.  Error message:' . print_r($e, true));
        			$transaction->rollBack();
        		}
        	}
        	/* when you need to debug non-client errors or those not associated with a field  */
            /*
        	$errors = print_r($model->errors, true) . print_r($modelAddress->errors, true) . print_r($modelPhone->errors, true);
        	throw new \Exception('Uncaught validation exception: ' . $errors);
        	*/
        }
        $modelAddress->address_type = OptionHelper::ADDRESS_MAILING; 
        return $this->render('create', [
                'model' => $model,
            	'modelAddress' => $modelAddress,
            	'modelPhone' => $modelPhone,
            	'modelEmail' => $modelEmail,
        		'modelStatus' => $modelStatus,
        		'modelClass' => $modelClass,
        ]);
    }
    
    public function actionCreateStub()
    {
    	$idGenerator = new MemberId();
    	$model = new Member([
    			'idGenerator' => $idGenerator, 
    			'scenario' => Member::SCENARIO_CREATE,
    			'local_pac' => OptionHelper::TF_FALSE,
    			'hq_pac' => OptionHelper::TF_FALSE,
    			'shirt_size' => 'U',
    			'birth_dt' => '1916-01-01',
    	]);
    	$modelStatus = new Status;
    	
    	if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
			Yii::$app->response->format = 'json';
			return ActiveForm::validate($model);
		}
		
    	if ($model->load(Yii::$app->request->post()) && $modelStatus->load(Yii::$app->request->post())) {
    		if ($model->validate()) {
    			$transaction = \Yii::$app->db->beginTransaction();
    			try {
    				if ($model->save(false)) {
    					$modelStatus->effective_dt = $model->application_dt;
    					$modelStatus->member_status = Status::STUB;
    					if ($model->addStatus($modelStatus)) {
    						$transaction->commit();
    						Yii::$app->session->setFlash('success', "Member stub successfully created");
    						return $this->goBack();
    					}
    						
        				Yii::$app->session->addFlash('error', 'Could not save Status. Check log for details. Code `MC010`');
        				Yii::error("*** MC010 Status save error.  Messages: " . print_r($modelStatus->errors, true));
    					    					    	
    				}
    					 
    				$transaction->rollBack();
        		} catch (\Exception $e) {
        			Yii::$app->session->addFlash('error', 'Could not save record. Check log for details. Code `MC020`');
        			Yii::error("*** MC020 Stub save error.  Messages: " . print_r($model->errors, true) . print_r($modelStatus->errors, true));
        			$transaction->rollBack();
        			
        		}
        		return $this->goBack();
    		}
    		/* when you need to debug non-client errors or those not associated with a field  */
    		/*
    		 $errors = print_r($model->errors, true) ;
    		 throw new \Exception('Uncaught validation exception: ' . $errors);
    	
    		*/
    	}		 

    	$this->initCreate($model);
    	if (!isset($model->ssnumber))
    		$model->ssnumber = '000-00-0000';
    	
    	return $this->renderAjax('create-stub', [
    			'model' => $model,
    			'modelStatus' => $modelStatus,
    	]);
    }

    /**
     * Updates an existing Member model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
    	parent::actionUpdate($id);
    	
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
        	Yii::$app->session->setFlash('success', "Member record successfully updated");
        	return $this->redirect(['view', 'id' => $model->member_id]);
        } 
        return $this->render('update', [
        	'model' => $model,
        	// Addresses, phones, emails and specialties are updated in their own controllers
            'modelsAddress' => $model->getAddresses(),
        	'modelsPhone' => $model->getPhones(),
        	'modelsEmail' => $model->getEmails(),
        	'modelsSpecialty' => $model->getSpecialties(),
        ]);
    }

    /**
     * Uploads a member's photo
     *
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionPhoto($id)
    {
    	$model = $this->findModel($id);
    	$oldPath = $model->imagePath;
    	$oldId = $model->photo_id;
    	
    	if ($model->load(Yii::$app->request->post())) {
    		$image = $model->uploadImage();
    		 
    		if($image === false)
    			$model->photo_id = $oldId;
    		 
    		if	($model->save()) {
    			if ($image !== false) {
    				if (file_exists($oldPath))
    					unlink($oldPath);
    				$path = $model->imagePath;
    				$image->saveAs($path);
    			}
    			return $this->redirect(['view', 'id' => $model->member_id]);
    		}
    	}
    	return $this->renderAjax('photo', ['model' => $model]);
    	 
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionPhotoClear($id)
    {
    	$model = $this->findModel($id);
    	$model->photo_id = null;
    	$model->save();
    	return $this->redirect(['view', 'id' => $model->member_id]);
    }

    /**
     * Deletes an existing Member model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->delete()) {
        	if (!$model->deleteImage())	
        		Yii::$app->session->setFlash('error', 'Could not delete image');
        }
        Yii::$app->session->setFlash('success', "Member record deleted");
        return $this->redirect(['index']);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIdCardPreview($id)
    {
//        $this->layout = 'noheadreport';
//        $this->layout = 'pvccard';
        $model = $this->findModel($id);
        if (Yii::$app->request->isAjax)
            return $this->renderAjax('id-card-preview', ['model' => $model]);
        return $this->render('id-card-preview', ['model' => $model]);

    }

    /**
     * @param $id
     * @param null $report_year
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionPrintPreview($id, $report_year = null)
    {
        $year = is_null($report_year) ? $this->getToday()->getYear() - 1 : $report_year;

        $this->layout = 'noheadreport';
        $model = $this->findModel($id);

        $typesSubmitted = ReceiptMember::getFeeTypesSubmitted($id, $year);

        $sqlProvider = new SqlDataProvider([
            'sql' => ReceiptMember::getFlattenedReceiptsByMemberSql($typesSubmitted, $year),
            'params' => [':member_id' => $id],
            'pagination' => false,
        ]);

        return $this->render('/member/print-preview', compact('model', 'sqlProvider', 'typesSubmitted'));
    }

    /**
     * List builder for member pickllist.  Builds JSON encoded array:
     * ['results'] key provides progressive results. If a member ID is provided,
     *               then this key provides the member_id and full_name
     *
     * @param string|array $search Criteria used.
     * @param string $member_id Selected member's ID
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionMemberList($search = null, $member_id = null) 
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	$out = ['results' => ['id' => '', 'text' => '']];
    	if (!is_null($search)) {
    		$data = Member::listAll($search);
    		$out['results'] = array_values($data);
    	}
    	elseif (!is_null($member_id) && ($member_id <> '0')) {
    		$out['results'] = ['member_id' => $member_id, 'text' => Member::findOne($member_id)->fullName];
    	}
    	return $out;    	
    }

    /**
     * List builder for member pickllist.  Builds JSON encoded array:
     * ['results'] key provides progressive results. If a member ID is provided,
     *               then this key provides the member_id and full_name
     *
     * @param string|array $search Criteria used.
     * @param string $lob_cd
     * @param string $member_id Selected member's ID
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionMemberSsnList($search = null, $lob_cd = null, $member_id = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($search)) {
            $condition = (is_null($lob_cd)) ? $search : ['full_nm' => $search, 'lob_cd' => $lob_cd];
            $data = Member::listSsnAll($condition);
            $out['results'] = array_values($data);
        }
        elseif (!is_null($member_id) && ($member_id <> '0')) {
            $out['results'] = ['member_id' => $member_id, 'text' => Member::findOne($member_id)->fullName];
        }
        return $out;
    }

    /**
     * Finds the Member model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Member the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Member::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    protected function createNote(Member $member)
    {
    	$note = new Note;
    	if (isset($_POST['Note'])) {
    		$note->attributes = $_POST['Note'];
    		if ($member->addNote($note)) {
    			$this->refresh();
    		}
    	}
    	return $note;
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
    	if (!isset($model->application_dt)) {
    		$model->application_dt = $this->today->getMySqlDate();
    	}
    }
    
    
    
}
