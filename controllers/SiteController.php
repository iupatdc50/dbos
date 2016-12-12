<?php
namespace app\controllers;

use app\models\user\LoginForm;
use app\models\user\User;
use app\models\Announcement;
use app\components\utilities\OpDate;
use \yii\web\Controller;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Response;

class SiteController extends Controller
{
	
	public $session;
	
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
	}
	
	public function actionIndex() {
    	$announcementModel = $this->createAnnouncement();
    	$announcements = Announcement::find()->orderBy(['created_at' => SORT_DESC])->all();
		return $this->render('homepage', [
				'announcementModel' => $announcementModel,
				'announcements' => $announcements, 
		]);
	} 
	
	/**
	 * Standard user login action
	 * 
	 * If successful, sets a session variable to hold last login time and 
	 * stores current login time. Also forces a password reset if the current
	 * password is User::RESET_USER_PW
	 * 
	 * @throws \Exception
	 * @return \yii\web\Response|Ambigous <string, string>
	 */
	public function actionLogin()
	{
		$this->layout = 'login';
		if (!\Yii::$app->user->isGuest)
			return $this->goHome();
			
		$model = new LoginForm();
		if ($model->load(Yii::$app->request->post()) && $model->login()) {
			/* @var $user User */
			$user = User::findByUsername(Yii::$app->user->identity->username);
			$this->session->set('user.last_login', $user->lastLoginDisplay);
			$user->last_login = $this->today->getMySqlDate(false);
			if (!$user->save(true, ['last_login']))
				throw new \Exception('Problem with last_login update.  Messages: ' . print_r($user->errors, true));
			if ($user->requiresReset())
				return $this->redirect('/user/reset-pw');
			return $this->goBack();
		}
	
		return $this->render('login', compact('model'));
	}
	
	public function actionLogout()
	{
		Yii::$app->user->logout();
		return $this->goHome();
	}	
	
	public function actionDelete($id)
	{
		$model = Announcement::findOne($id);
		if (!$model) 
			throw new NotFoundHttpException('The requested page does not exist.');
		$model->delete();
		return $this->goBack();
	}
	
	public function actionUnavailable()
	{
		return $this->render('unavailable');
	}
	
	public function actionTerms()
	{
		$this->layout = 'aux';
		return $this->render('terms');
	}
	
	protected function createAnnouncement()
	{
		$announcement = new Announcement;
		if (isset($_POST['Announcement'])) {
			$announcement->attributes = $_POST['Announcement'];
			if ($announcement->save()) 
				$this->refresh();
		}
		return $announcement;
	}
	
	
	
}