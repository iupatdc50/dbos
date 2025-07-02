<?php

namespace app\controllers;

use app\models\accounting\GeneratedBill;
use app\models\contractor\Email;
use Throwable;
use Yii;
use app\controllers\base\RootController;
use app\models\contractor\Contractor;
use app\models\contractor\Address;
use app\models\contractor\Phone;
use app\models\contractor\ContractorSearch;
use app\models\contractor\Signatory;
use app\models\contractor\Note;
use app\models\employment\Employment;
use app\models\employment\EmploymentSearch;
use app\models\accounting\CreateRemitForm;
use app\models\accounting\StagedBill;
use app\models\accounting\TradeFeeType;
use app\modules\admin\models\FeeType;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\helpers\OptionHelper;
use yii\web\Response;
use yii\web\UploadedFile;


/**
 * ContractorController implements the CRUD actions for Contractor model.
 */
class ContractorController extends RootController
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
        		'only' => ['index', 'view', 'create', 'update', 'delete', 'create-remit'],
        		'rules' => [
        			[
        				'allow' => true,
        				'actions' => ['index', 'view'],
        				'roles' => ['browseContractor'],
        			],
        			[
        				'allow' => true,
        				'actions' => ['create'],
        				'roles' => ['createContractor'],
        			],
        			[
        				'allow' => true,
        				'actions' => ['update'],
        				'roles' => ['updateContractor'],
        			],
        			[
        				'allow' => true,
        				'actions' => ['create-remit', 'remit-template'],
        				'roles' => ['createInvoice'],
        			],
        			[
        				'allow' => true,
        				'actions' => ['delete'],
        				'roles' => ['deleteContractor'],
        			],
        		],
        	],
        		
        ];
    }

    /**
     * Lists all Contractor models.
     * @return string
     */
    public function actionIndex()
    {
        /** @noinspection PhpExpressionResultUnusedInspection */
        $this->storeReturnUrl();
    	 
    	$searchModel = new ContractorSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionCreateRemit($id)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $modelContractor = $this->findModel($id);
    	$remitForm = new createRemitForm();
    	
    	if ($remitForm->load(Yii::$app->request->post()) && $remitForm->validate()) {
    		
    		$employees = Employment::find()
    			->joinWith(['member', 'member.currentStatus'])
    			->where([
    					Employment::tableName() . '.end_dt' => null,
    					'dues_payor' => $remitForm->license_nbr,
    					'lob_cd' => $remitForm->lob_cd,
    			])
    			->all()
    		;
    		
    		$invalid = '';
    		foreach ($employees as $employee) {
    		    /* @var Employment $employee */
	    		$member = $employee->member;
	    		if ($member->isInApplication() && (!isset($employee->member->currentApf)))
	    				$invalid .= '<li>' .$member->report_id . ': ' . $member->fullName . '</li>';
    		}
    			
    		if (empty($invalid)) {
	    		return $this->redirect([
	    				'remit-template',
	    				'id' => $remitForm->license_nbr,
	    				'lob_cd' => $remitForm->lob_cd,
                        'remarks' => $remitForm->remarks,
	    		]);
    		} else {
    			$message = 'The following members are in application but have no current APF Assessment: <ul>' . $invalid . '</ul>';
    			Yii::$app->session->addFlash('error', $message);
    			return $this->goBack();
    		}
    	}
    	
    	$remitForm->license_nbr = $modelContractor->license_nbr;
    	return $this->renderAjax('create-remit', [
    			'remitForm' => $remitForm,
    			'modelContractor' => $modelContractor,
    	]);
    }

    /**
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionLobPicklist()
    {
    	if (isset($_POST['depdrop_parents'])) {
    		$parents = $_POST['depdrop_parents'];
    		if ($parents != null) {
                $modelContractor = $this->findModel($parents[0]);
    			$out = $modelContractor->currentLobOptions;
    			$selected = '';
    			if (!empty($out) && count($out) > 0)
    				$selected = $out[0]['id'];
                return $this->asJson(['output'=>$out, 'selected'=> $selected]);
    		}
    	}
        return $this->asJson(['output'=>'', 'selected'=>'']);
    					 
    }

    /**
     * Builds exported spreadsheet from Employer model
     *
     * @param string $id Contractor ID
     * @param string $lob_cd
     * @param null $remarks
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionRemitTemplate($id, $lob_cd, $remarks = null)
    {
    	$modelContractor = $this->findModel($id);
        $stagedBillModel = new StagedBill();
        $employees = $stagedBillModel->getBillableCount($id, $lob_cd);

    	$audit = new GeneratedBill([
    	    'license_nbr' => $id,
            'lob_cd' => $lob_cd,
            'employees' => $employees,
            'remarks' => $remarks,
        ]);
    	$audit->save();

        /** add removal of IUPAT PAC entries from all but Glaziers */
        /** ->andwhere(['or', 'fee_type<>"IU"', 'lob_cd="1889"']) */
        /** fee_type filter is temporary until later this year 7/2/25 tspeed */
    	$modelsFeeType = TradeFeeType::find()
            ->where(['lob_cd' => $lob_cd, 'employer_remittable' => true])
            ->andWhere(['!=', 'fee_type', FeeType::TYPE_IUPAT])
            ->orderBy('seq')
            ->all();

    	$dataProvider = $stagedBillModel->getPreFill($id, $lob_cd);
    	return $this->renderPartial('remit-template', [
    			'dataProvider' => $dataProvider, 
    			'modelContractor' => $modelContractor, 
    			'lob_cd' => $lob_cd,
    			'modelsFeeType' => $modelsFeeType,
                'doc_number' => $audit->id,
    	]);
    	
    }

    /**
     * Displays a single Contractor model.
     * @param string $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
    	parent::actionView($id);
    	 
    	$model = $this->findModel($id);

    	$employeeSearchModel = new EmploymentSearch();
    	$employeeSearchModel->employer_search = $id;
    	
    	$employeeProvider = $employeeSearchModel->search(Yii::$app->request->queryParams);
    	
    	return $this->render('view', [
            'model' => $model,
            'employeeProvider' => $employeeProvider,
    		'employeeSearchModel' => $employeeSearchModel,
    		'noteModel' => $this->createNote($model),
        ]);
    }

    /**
     * Creates a new Contractor model.
     *
     * Allows a single address and phone on initial create
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return Response|string
     */
    public function actionCreate()
    {
        $model = new Contractor;
        $modelSig = new Signatory;
        $modelAddress = new Address(['set_as_default' => true]);
        $modelPhone = new Phone(['set_as_default' => true]);
        $modelEmail = new Email;
        
        if ($model->load(Yii::$app->request->post()) 
        		&& $modelSig->load(Yii::$app->request->post())
        		&& $modelAddress->load(Yii::$app->request->post()) 
        		&& $modelPhone->load(Yii::$app->request->post())
                && $modelEmail->load(Yii::$app->request->post())) {
        				 
        	if ($model->validate() && $modelSig->validate() && $modelAddress->validate() && $modelPhone->validate() && $modelEmail->validate()) {
        		$transaction = Yii::$app->db->beginTransaction();
        		try {
        			if ($model->save(false)) {

                        if (isset($modelEmail->email)) {
                            $modelEmail->contractor = $model;
                            $modelEmail->save(false);
                        }

                        $modelSig->license_nbr = $model->license_nbr;
                        /* @var $image UploadedFile */
        				$image = $modelSig->uploadImage();
        				$modelAddress->license_nbr = $model->license_nbr;
        				$modelPhone->license_nbr = $model->license_nbr;
        				if ($modelSig->save(false) && $modelAddress->save(false) && $modelPhone->save(false)) {
        					if ($image != false) {
        						$path = $modelSig->imagePath;
        						$image->saveAs($path);
        					}
        					$transaction->commit();
        					Yii::$app->session->setFlash('success', "Contractor record successfully created");
        					return $this->redirect(['view', 'id' => $model->license_nbr]);
        				}
        			}
        			$transaction->rollBack();
        		} catch (\Exception $e) {
        			Yii::$app->session->addFlash('error', 'Could not save record. Possible duplicate License Number. [errno: ' . $e->getCode() . ']');
        			$transaction->rollBack();
        		}
        	}
        	/* when you need to debug non-client errors or those not associated with a field  */
			/*
        	$errors = print_r($model->errors, true) . print_r($modelSig->errors, true) . print_r($modelAddress->errors, true) . print_r($modelPhone->errors, true);
        	throw new \Exception('Uncaught validation exception: ' . $errors);
        	*/
        }

        if (!isset($modelAddress->address_type))
            $modelAddress->address_type = OptionHelper::ADDRESS_MAILING;
        if (!isset($modelEmail->email_type))
            $modelEmail->email_type = Email::TYPE_CONTACT;
        return $this->render('create', [
                'model' => $model,
            	'modelSig' => $modelSig,
        		'modelAddress' => $modelAddress,
            	'modelPhone' => $modelPhone,
                'modelEmail' => $modelEmail,
        ]);
    }

    /**
     * Updates an existing Contractor model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return Response|string
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
    	parent::actionUpdate($id);
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
        	Yii::$app->session->setFlash('success', "Contractor record successfully updated");
            return $this->redirect(['view', 'id' => $model->license_nbr]);
        } 
        return $this->render('update', [
        	'model' => $model,
        	// Addresses, phones and emails are updated in their own controllers
            'modelsAddress' => $model->getAddresses(),
        	'modelsPhone' => $model->getPhones(),
            'modelsEmail' => $model->getEmails(),
        ]);
    }

    /**
     * Deletes an existing Contractor model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        Yii::$app->session->setFlash('success', "Contractor record deleted");
        return $this->redirect(['index']);
    }

    /**
     * List builder for contractor pickllist.  Builds JSON encoded array:
     * ['results'] key provides progressive results. If a license number is provided,
     *               then this key provides the license_nbr and contractor name
     *
     * @param string|array $search Criteria used.
     * @param null $agreement_type
     * @param string $license_nbr Selected contractor's license number
     * @return Response
     * @throws Exception
     */
    public function actionContractorList($search = null, $agreement_type = null, $license_nbr = null) 
    {
    	$out = ['results' => ['id' => '', 'text' => '']];
    	if (!is_null($search)) {
    		$condition = (is_null($agreement_type)) ? $search : ['contractor' => $search, 'agreement_type' => $agreement_type];
    		$data = Contractor::listAll($condition);
    		$out['results'] = array_values($data);
    	}
    	elseif (!is_null($license_nbr) && ($license_nbr <> '0')) {
    		$out['results'] = ['license_nbr' => $license_nbr, 'text' => Contractor::findOne($license_nbr)->contractor];
    	}
    	return $this->asJson($out);
    }

    /**
     * Finds the Contractor model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Contractor the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Contractor::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function createNote(Contractor $contractor)
    {
    	$note = new Note;
    	if (isset($_POST['Note'])) {
    		$note->attributes = $_POST['Note'];
    		if ($contractor->addNote($note)) {
    			$this->refresh();
    		}
    	}
    	return $note;
    }
    
    
}
