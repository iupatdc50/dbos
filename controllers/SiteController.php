<?php
namespace app\controllers;

use app\controllers\base\RootController;
use app\models\user\LoginForm;
use app\models\user\User;
use app\models\Announcement;
use app\models\HomeEvent;
use Exception;
use Throwable;
use Yii;
use app\helpers\OptionHelper;
use yii\db\StaleObjectException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii2fullcalendar\models\Event;

/** @noinspection PhpUnused */

class SiteController extends RootController
{

    /*
    public function actions() {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
//                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    */
	
	public function actionIndex() {

		$homeEvents = HomeEvent::find()->all();
		$events = [];
		/* @var $homeEvent HomeEvent */
		foreach ($homeEvents as $homeEvent) {
			$event = new Event([
					'id' => $homeEvent->id,
					'title' => $homeEvent->title,
					'allDay' => ($homeEvent->all_day == OptionHelper::TF_TRUE),
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

	/** @noinspection PhpUnused */
	/**
	 * Standard user login action
	 * 
	 * If successful, sets a session variable to hold last login time and 
	 * stores current login time. Also forces a password reset if the current
	 * password is User::RESET_USER_PW
	 * 
	 * @throws Exception
	 * @return string
     */
	public function actionLogin()
	{
		$this->layout = 'login';
		if (!Yii::$app->user->isGuest) {
			return $this->goHome();
		}
			
		$model = new LoginForm();
		if ($model->load(Yii::$app->request->post()) && $model->login()) {
			/* @var $user User */
			$this->setSessionInfo();
			$user = $this->getUser();
			$user->last_login = $this->today->getMySqlDate(false);
			if (!$user->save(true, ['last_login']))
				throw new Exception('Problem with last_login update.  Messages: ' . print_r($user->errors, true));
			if ($user->requiresReset()) {
			    $user->expirePasswordResetToken();
                return $this->redirect('/admin/user/reset-pw');
            }

			return $this->goBack();
		}
	
		return $this->render('login', compact('model'));
	}

    /** @noinspection PhpUnused */
	public function actionLogout()
	{
		Yii::$app->user->logout();
		return $this->goHome();
	}

    /**
     * @param $id
     * @return Response
     * @throws StaleObjectException
     * @throws NotFoundHttpException
     * @throws Throwable
     */
	public function actionDelete($id)
	{
		$model = Announcement::findOne($id);
		if (!$model) 
			throw new NotFoundHttpException('The requested page does not exist.');
		$model->delete();
		return $this->goHome();
	}

    /** @noinspection PhpUnused */
	public function actionUnavailable()
	{
		return $this->render('unavailable');
	}

    /** @noinspection PhpUnused */
	public function actionTerms()
	{
		$this->layout = 'aux';
		return $this->render('terms');
	}

    /** @noinspection PhpUnused */
	public function actionMaintenance()
    {
        return $this->renderPartial('maintenance');
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