<?php

namespace app\controllers;

use Yii;
use app\controllers\base\RootController;
use app\models\member\Member;
use app\models\member\MemberId;
use app\models\member\Address;
use app\models\member\Phone;
use app\models\member\MemberSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use app\models\member\Status;
use app\models\member\MemberClass;
use app\models\member\Employment;
use app\models\member\Note;
use app\models\member\StatusCode;
use yii\helpers\ArrayHelper;

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

    	$statusModel = Status::findCurrent($id);
    	$classModel = MemberClass::findCurrent($id);
    	$employerModel = Employment::findCurrent($id);
    	$noteModel = $this->createNote($model);

        return $this->render('view', [
            'model' => $model,
            'statusModel' => $statusModel,
            'classModel' => $classModel,
        	'employerModel' => $employerModel,
        	'noteModel' => $noteModel,
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
        
        if ($model->load(Yii::$app->request->post())
        		&& $modelAddress->load(Yii::$app->request->post()) 
        		&& $modelPhone->load(Yii::$app->request->post())) {
        	
        	$image = $model->uploadImage();
        	if ($model->validate() && $modelAddress->validate() && $modelPhone->validate()) {
        		 
        		$transaction = \Yii::$app->db->beginTransaction();
        		try {
        			if ($model->save(false)) {
        				if ($image !== false) {
		        			$path = $model->imagePath;
		        			$image->saveAs($path);
		        		}
        				$modelAddress->member_id = $model->member_id;
						$modelPhone->member_id = $model->member_id;        				

        				if ($modelAddress->save(false) && $modelPhone->save(false)) {
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
        	/* should not reach this */
        	$errors = print_r($model->errors, true) . print_r($modelAddress->errors, true) . print_r($modelPhone->errors, true);
        	throw new \Exception('Uncaught validation exception: ' . $errors);
        } 
        return $this->render('create', [
                'model' => $model,
            	'modelAddress' => $modelAddress,
            	'modelPhone' => $modelPhone,
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
        	// Addresses, phones and specialties are updated in their own controllers
            'modelsAddress' => $model->getAddresses(),
        	'modelsPhone' => $model->getPhones(),
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
