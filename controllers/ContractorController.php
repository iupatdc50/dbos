<?php

namespace app\controllers;

use Yii;
use app\controllers\base\RootController;
use app\models\contractor\Contractor;
use app\models\contractor\Address;
use app\models\contractor\Phone;
use app\models\contractor\ContractorSearch;
use app\models\contractor\Signatory;
use app\models\member\EmploymentSearch;
use app\models\accounting\CreateRemitForm;
use app\models\accounting\TradeFeeType;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;
use app\helpers\OptionHelper;
use yii\data\yii\data;

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
        ];
    }

    /**
     * Lists all Contractor models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ContractorSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionCreateRemit($id)
    {
    	$modelContractor = $this->findModel($id);
    	$remitForm = new createRemitForm();
    	if ($remitForm->load(Yii::$app->request->post()) && $remitForm->validate()) {
    		return $this->redirect([
    				'remit-template',
    				'id' => $remitForm->license_nbr,
    				'lob_cd' => $remitForm->lob_cd,
    		]);
    	}
    	$remitForm->license_nbr = $modelContractor->license_nbr;
    	return $this->renderAjax('create-remit', [
    			'remitForm' => $remitForm,
    			'modelContractor' => $modelContractor,
    	]);
    }
    
    public function actionLobPicklist()
    {
    	$out = [];
    	if (isset($_POST['depdrop_parents'])) {
    		$parents = $_POST['depdrop_parents'];
    		if ($parents != null) {
    			$modelContractor = $this->findModel($parents[0]);
    			$out = $modelContractor->currentLobOptions;
    			$selected = '';
    			if (!empty($out) && count($out) > 0)
    				$selected = $out[0]['id'];
    			echo Json::encode(['output'=>$out, 'selected'=> $selected]);
    			return;
    		}
    	}
    	echo Json::encode(['output'=>'', 'selected'=>'']);
    					 
    } 
    
    /**
     * Builds exported spreadsheet from Employer model
     * 
     * @param string $id Contractor ID
     * @param string $lob_cd
     * @return string
     */
    public function actionRemitTemplate($id, $lob_cd)
    {
    	$modelContractor = $this->findModel($id);
    	$modelsFeeType = TradeFeeType::find()->where(['lob_cd' => $lob_cd, 'employer_remittable' => true])->all();
    	/* @var $query yii\db\ActiveQuery */
    	$employeeSearchModel = new EmploymentSearch([
    			'employer_search' => $id,
    			'lob_cd' => $lob_cd,
    			'page_size' => 2000,
    	]);
    	
 		$dataProvider = $employeeSearchModel->search([]);
    	return $this->renderPartial('remit-template', [
    			'dataProvider' => $dataProvider, 
    			'modelContractor' => $modelContractor, 
    			'lob_cd' => $lob_cd,
    			'modelsFeeType' => $modelsFeeType,
    	]);
    	
    }

    /**
     * Displays a single Contractor model.
     * @param string $id
     * @return mixed
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
        ]);
    }

    /**
     * Creates a new Contractor model.  
     * 
     * Allows a single address and phone on initial create
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Contractor;
        $modelSig = new Signatory;
        $modelAddress = new Address;
        $modelPhone = new Phone;
        
        if ($model->load(Yii::$app->request->post()) 
        		&& $modelSig->load(Yii::$app->request->post())
        		&& $modelAddress->load(Yii::$app->request->post()) 
        		&& $modelPhone->load(Yii::$app->request->post())) {
        				 
        	if ($model->validate() && $modelSig->validate() && $modelAddress->validate() && $modelPhone->validate()) {
        		$transaction = \Yii::$app->db->beginTransaction();
        		try {
        			if ($model->save(false)) {
        				$modelSig->license_nbr = $model->license_nbr;
        				$image = $modelSig->uploadImage();
        				$modelAddress->license_nbr = $model->license_nbr;
        				$modelPhone->license_nbr = $model->license_nbr;
        				if ($modelSig->save(false) && $modelAddress->save(false) && $modelPhone->save(false)) {
        					if ($image !== false) {
        						$path = $modelSig->imagePath;
        						$image->saveAs($path);
        					}
        					$transaction->commit();
        					return $this->redirect(['view', 'id' => $model->license_nbr]);
        				}
        			}
        			$transaction->rollBack();
        		} catch (\Exception $e) {
        			$transaction->rollBack();
        		}
        	}
        }
        
        $modelAddress->address_type = OptionHelper::ADDRESS_MAILING;
        return $this->render('create', [
                'model' => $model,
            	'modelSig' => $modelSig,
        		'modelAddress' => $modelAddress,
            	'modelPhone' => $modelPhone,
        ]);
    }

    /**
     * Updates an existing Contractor model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
    	parent::actionUpdate($id);
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->license_nbr]);
        } 
        return $this->render('update', [
        	'model' => $model,
        	// Addresses and phones are updated in their own controllers
            'modelsAddress' => $model->getAddresses(),
        	'modelsPhone' => $model->getPhones(),
        ]);
    }

    /**
     * Deletes an existing Contractor model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    /**
     * List builder for contractor pickllist.  Builds JSON encoded array:
     * ['results'] key provides progressive results. If a license number is provided,
     * 			   then this key provides the license_nbr and contractor name 	
     * 
     * @param string|array $search Criteria used. 
     * @param string $license_nbr Selected contractor's license number
     */
    public function actionContractorList($search = null, $agreement_type = null, $license_nbr = null) 
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	$out = ['results' => ['id' => '', 'text' => '']];
    	if (!is_null($search)) {
    		$condition = (is_null($agreement_type)) ? $search : ['contractor' => $search, 'agreement_type' => $agreement_type];
    		$data = Contractor::listAll($condition);
    		$out['results'] = array_values($data);
    	}
    	elseif (!is_null($license_nbr) && ($license_nbr <> '0')) {
    		$out['results'] = ['license_nbr' => $license_nbr, 'text' => Contractor::findOne($license_nbr)->contractor];
    	}
    	return $out;    	
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
    
}
