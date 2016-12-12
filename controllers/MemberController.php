<?php

namespace app\controllers;

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
use app\models\member\AllowableClassDescription;
use app\models\member\Employment;
use app\models\member\Note;
use app\models\member\MemberSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use app\helpers\OptionHelper;

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
	        	'only' => ['index', 'view', 'create', 'update', 'delete', 'photo'],
	            'rules' => [
	                [
	                    'allow' => true,
	                    'actions' => ['index', 'view'],
	                    'roles' => ['browseMember'],
	                ],
	                [
	                    'allow' => true,
	                    'actions' => ['create'],
	                    'roles' => ['createMember'],
	                ],
	                [
	                    'allow' => true,
	                    'actions' => ['update', 'photo'],
	                    'roles' => ['updateMember'],
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
     */
    public function actionView($id)
    {
    	parent::actionView($id);
    	
    	$model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        	'noteModel' => $this->createNote($model),
        ]);
    }

    /**
     * Creates a new Member model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
    	$idGenerator = new MemberId();
        $model = new Member(['idGenerator' => $idGenerator]);
        $modelAddress = new Address;
        $modelPhone = new Phone;
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
        	if ($model->validate() && $modelAddress->validate() && $modelPhone->validate()) {
        		
        		$model->init_dt = ($model->exempt_apf) ? $model->application_dt : null;
        		 
        		$transaction = \Yii::$app->db->beginTransaction();
        		try {
        			if ($model->save(false)) {
        				if ($image !== false) {
		        			$path = $model->imagePath;
		        			$image->saveAs($path);
		        		}
        				$modelAddress->member_id = $model->member_id;
						$modelPhone->member_id = $model->member_id;        				
						$modelEmail->member = $model;
						
        				if ($modelAddress->save(false) && $modelPhone->save(false) && $modelEmail->save(false)) {
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
        						if(!$model->createApfAssessment())
        							throw new \Exception('Uncaught errors saving assessment');
        					}
        					$transaction->commit();
							return $this->redirect(['view', 'id' => $model->member_id]);
        				}
        			}
        		    $transaction->rollBack();
        		} catch (\Exception $e) {
        			$transaction->rollBack();
        			throw new \Exception('Error when trying to save created Member: ' . $e);
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

    /**
     * Updates an existing Member model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
    	parent::actionUpdate($id);
    	
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
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
     * Deletes an existing Member model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->delete()) {
        	if (!$model->deleteImage())	
        		Yii::$app->session->setFlash('error', 'Could not delete image');
        }
        return $this->redirect(['index']);
    }

    /**
     * List builder for member pickllist.  Builds JSON encoded array:
     * ['results'] key provides progressive results. If a member ID is provided,
     * 			   then this key provides the member_id and full_name 	
     * 
     * @param string|array $search Criteria used. 
     * @param string $member_id Selected member's ID
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
    
}
