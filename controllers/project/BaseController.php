<?php

namespace app\controllers\project;

use Yii;
use app\controllers\base\RootController;
use app\models\base\Model;
use app\models\project\ProjectId;
use app\models\project\Address;
use app\models\project\Note;
use app\models\project\CancelForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use app\models\project\jtp\Registration;
use app\helpers\OptionHelper;

/**
 * Implements the CRUD actions for a Project model.
 */
class BaseController extends RootController
{
	private $_recordClass;
	private $_recordSearchClass;
	private $_registrationClass;
	
	protected $model;
	/** @var array Supplemental data providers in agreement type classes */
	protected $otherProviders = [];
	/** @var string Agreement type */
	protected $type;
	
	public function init()
	{
		// Use agreement type to build specific model names
		$this->_recordClass = 'app\models\project\\' . strtolower($this->type) . '\Project';
		$this->_recordSearchClass = 'app\models\project\\' . strtolower($this->type) . '\ProjectSearch';
		$this->_registrationClass = 'app\models\project\\' . strtolower($this->type) . '\Registration';
	}
	
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
     * Lists all Project models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new $this->_recordSearchClass();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        $config = [
        	'searchModel' => $searchModel,
            'dataProvider' => $dataProvider, 
        ];
        foreach ($this->otherProviders as $label => $provider)
        	$config[$label] = $provider;
        
        return $this->render('index', $config);
    }

    /**
     * Displays a single Project model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
    	
    	parent::actionView($id);
    	
    	if (!isset($this->model))
    		$this->model = $this->findModel($id);
    	$registrationProvider = new ActiveDataProvider([
    			'query' => $this->model->getRegistrations(),
				'sort' => false,
    	]);
    	$noteModel = $this->createNote($this->model);
    	$config = [
            'model' => $this->model,
    		'registrationProvider' => $registrationProvider,
        	'noteModel' => $noteModel,
    	];
    	foreach ($this->otherProviders as $label => $provider)
    		$config[$label] = $provider;
    	return $this->render('view', $config);
    }

    /**
     * Creates a new Project model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $idGenerator = new ProjectId();
    	$model = new $this->_recordClass(['idGenerator' => $idGenerator]);
        $modelAddress = new Address;
        $modelRegistration = new $this->_registrationClass;
 
        if ($model->load(Yii::$app->request->post()) 
        		&& $modelAddress->load(Yii::$app->request->post())
        		&& $modelRegistration->load(Yii::$app->request->post())) {

        	$model->agreement_type = $this->type;
        			 
        	if ($model->validate() && $modelAddress->validate() && $modelRegistration->validate()) {
        		$transaction = \Yii::$app->db->beginTransaction();
        		try {
        			if ($model->save(false)) {
        				$modelAddress->project_id = $model->project_id;
        				$modelRegistration->project_id = $model->project_id;

        				$image = $modelRegistration->uploadImage();
        				
        				if ($modelAddress->save(false) && $modelRegistration->save(false)) {
        					
        					if ($image !== false) {
        						$path = $modelRegistration->imagePath;
        						$image->saveAs($path);
        					}
        					
        					$transaction->commit();
        					return $this->redirect(['view', 'id' => $model->project_id]);
        				}
        			}
        			$transaction->rollBack();
        		} catch (\Exception $e) {
        			$transaction->rollBack();
        		}
        	}
			/* should not reach this */
	        $errors = PHP_EOL . '$model: ' . print_r($model->errors, true)
	        		. PHP_EOL . '$modelAddress: ' . print_r($modelAddress->errors, true)
	        		. PHP_EOL . '$modelRegistration: ' . print_r($modelRegistration->errors, true);
	        throw new \Exception('Models did not validate' . $errors);
        }
        
        $modelAddress->address_type = OptionHelper::ADDRESS_MAILING; 
        return $this->render('create', [
                'model' => $model,
        		'modelAddress' => $modelAddress,
            	'modelRegistration' => $modelRegistration,
        ]);
        
    }

    /**
     * Updates an existing Project model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
    	parent::actionUpdate($id);
    	$model = $this->findModel($id);
    	 
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
            return $this->redirect(['view', 'id' => $model->project_id]);
        return $this->render('update', [
        	'model' => $model,
        	// Addresses are updated in their own controller
        	'modelsAddress' => $model->getAddresses(),        		
        ]);
    }

    /**
     * Deletes an existing Project model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    public function actionCancel($id)
    {
    	$model = new CancelForm;
    	if ($model->load(Yii::$app->request->post())) {
    		$project = $this->findModel($id);
    		$project->project_status = 'X';
    		$project->close_dt = $model->cancel_dt;
    		if ($project->save()) {
    			$note = new Note;
    			$note->project_id = $id;
    			$note->note = "[CANCELLED {$model->cancel_dt}]: {$model->reason}";
    			$note->save();
    			return $this->goBack();
    		}
    	}
    	return $this->renderAjax('/project/cancel', compact('model'));
    }

    /**
     * Finds the Project model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Project the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = call_user_func([$this->_recordClass, 'findOne'], $id);
        if (!$model) {
        	throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $model;
    }
    
    protected function createNote($project)
    {
    	if (!($project instanceof $this->_recordClass))
    		throw new \BadMethodCallException('Not an instance of Project');
    	$note = new Note;
    	if (isset($_POST['Note'])) {
    		$note->attributes = $_POST['Note'];
    		if ($project->addNote($note)) {
    			$this->refresh();
    		}
    	}
    	return $note;
    }
    
}
