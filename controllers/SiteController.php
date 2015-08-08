<?php
namespace app\controllers;

use app\models\user\LoginForm;
use app\models\Announcement;
use \yii\web\Controller;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Response;

class SiteController extends Controller
{
	public function actionIndex() {
    	$announcementModel = $this->createAnnouncement();
    	$announcements = Announcement::find()->orderBy(['created_at' => SORT_DESC])->all();
		return $this->render('homepage', [
				'announcementModel' => $announcementModel,
				'announcements' => $announcements, 
		]);
	}
	
	public function actionLogin()
	{
		if (!\Yii::$app->user->isGuest)
			return $this->goHome();
	
		$model = new LoginForm();
		if ($model->load(Yii::$app->request->post()) && $model->login())
			return $this->goBack();
	
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