<?php

namespace app\controllers\base;

use Yii;
use yii\web\Controller;
use app\components\utilities\OpDate;
use app\models\user\User;
use yii\web\Session;

/**
 * Standard extension for models manipulated by subcontrollers
 */
class RootController extends Controller
{

    /* @var $session Session */
	public $session;
	public $user;
	
	/* @var $today OpDate */
	public $today;
	
	public function init()
	{
		if (!isset($this->session)) 
			$this->session = Yii::$app->session;
		if (!isset($this->today))
			$this->today = new OpDate;
		if (!Yii::$app->user->isGuest && !isset($this->session['user.last_login']))
			$this->setSessionInfo();
	}
	
	public function actionView(/** @noinspection PhpUnusedParameterInspection */ $id)
	{
		$this->storeReturnUrl();
	}
	
	public function actionUpdate(/** @noinspection PhpUnusedParameterInspection */ $id)
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
    	$session_start = $this->today->getMySqlDate(false);
    	$user = $this->getUser();
    	$this->session->set('user.last_login', $user->lastLoginDisplay);
    	$msg = "User `{$user->username}` started session on `{$session_start}`";
    	Yii::info($msg);
    	$this->session->set('user.session_start', $session_start);
    }
    
    protected function getUser()
    {
    	if(!isset($this->user) && !Yii::$app->user->isGuest)
            /** @noinspection PhpUndefinedFieldInspection */
            $this->user = User::findByUsername(Yii::$app->user->identity->username);
    	return $this->user;
    }
		
}