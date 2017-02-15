<?php

namespace app\controllers\base;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\utilities\OpDate;
use app\models\user\User;

/**
 * Standard extension for models manipulated by subcontrollers
 */
class RootController extends Controller
{
	
	public $session;
	public $user;
	
	/*
	 * OpDate
	 */
	public $today;
	
	public function init()
	{
		if (!isset($this->session)) 
			$this->session = Yii::$app->session;
		if (!isset($this->today))
			$this->today = new OpDate;
		if (!\Yii::$app->user->isGuest && !isset($this->session['user.last_login']))
			$this->setSessionInfo();
	}
	
	public function actionView($id)
	{
		$this->storeReturnUrl();
	}
	
	public function actionUpdate($id)
	{
		$this->storeReturnUrl();
	}
	
	/**
     * Allows GoBack() to return to the sending page instead of the home page
     */
    protected function storeReturnUrl()
    {
        Yii::$app->user->returnUrl = Yii::$app->request->url;
    }
    
    protected function setSessionInfo()
    {
    	$user = $this->getUser();
    	$this->session->set('user.last_login', $user->lastLoginDisplay);
    	$msg = "User `{$user->username}` started session on `{$this->today->getMySqlDate(false)}`";
    	Yii::info($msg);
    }
    
    protected function getUser()
    {
    	if(!isset($this->user) && !\Yii::$app->user->isGuest)
    		$this->user = User::findByUsername(Yii::$app->user->identity->username);
    	return $this->user;
    }
		
}