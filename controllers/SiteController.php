<?php
namespace app\controllers;

use app\controllers\base\RootController;
use app\models\user\LoginForm;
use app\models\user\User;
use app\models\Announcement;
use app\models\HomeEvent;
use app\components\utilities\OpDate;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use app\components\utilities\app\components\utilities;
use app\helpers\OptionHelper;

class SiteController extends RootController
{
	
	public function actionIndex() {

		$homeEvents = HomeEvent::find()->all();
		$events = [];
		foreach ($homeEvents as $homeEvent) {
			$event = new \yii2fullcalendar\models\Event([
					'id' => $homeEvent->id,
					'title' => $homeEvent->title,
					'allDay' => ($homeEvent->all_day == OptionHelper::TF_TRUE) ? true : false,
					'start' => $homeEvent->start_dt,
					'end' => $homeEvent->end_dt,
					'editable' => true,
					'className' => ($homeEvent->all_day == OptionHelper::TF_TRUE) ? 'fc-event-allday' : null,
			]);
			$events[] = $event;
		}
    	$announcementModel = $this->createAnnouncement();
    	$announcements = Announcement::find()->orderBy(['created_at' => SORT_DESC])->all();
		return $this->render('homepage', [
				'events' => $events,
				'announcementModel' => $announcementModel,
				'announcements' => $announcements, 
				'bypass_doc' => true,
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
	 * @return string
     */
	public function actionLogin()
	{
		$this->layout = 'login';
		if (!\Yii::$app->user->isGuest) {
			return $this->goHome();
		}
			
		$model = new LoginForm();
		if ($model->load(Yii::$app->request->post()) && $model->login()) {
			/* @var $user User */
			$this->setSessionInfo();
			$user = $this->getUser();
			$user->last_login = $this->today->getMySqlDate(false);
			$msg = "User `{$user->username}` successfully logged in on `{$user->last_login}`";
			Yii::info($msg);
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
		return $this->goHome();
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