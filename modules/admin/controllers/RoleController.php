<?php

namespace app\modules\admin\controllers;

use app\models\user\AssignmentForm;
use Exception;
use kartik\form\ActiveForm;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use app\models\rbac\AuthItemChild;
use app\models\user\User;

class RoleController extends Controller
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
                'only' => ['add', 'revoke'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['add', 'revoke'],
                        'roles' => ['assignRole'],
                    ],
                ],
            ],

        ];
    }


    public function actionSummaryAjax()
	{
		$key = $_POST['expandRowKey'];
		$query = AuthItemChild::find()->where(['parent' => $key['item_name']]);
		$dataProvider = new ActiveDataProvider(['query' => $query]);
		return $this->renderAjax('_summary', ['dataProvider' => $dataProvider]);
	}

    /**
     * @param $user_id
     * @return array|string
     * @throws Exception
     */
	public function actionAdd($user_id)
    {
        $model = new AssignmentForm([
            'user' => User::findOne($user_id),
            'staff_roles_only' => true,
        ]);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = 'json';
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            $auth = Yii::$app->authManager;
            if ($model->staff_roles_only) {
                $auth->revokeAll($user_id);
                $this->assignRole($auth, $model->staff_role, $user_id);
            } else {
                foreach ($model->action_roles as $role)
                    $this->assignRole($auth, $role, $user_id);
            }

            return $this->goBack();
        }

        return $this->renderAjax('add', ['model' => $model]);
    }

    public function actionRevoke($role, $user_id)
    {
        $auth = Yii::$app->authManager;
        $roleObject = $auth->getRole($role);
        if (!$roleObject) {
            throw new InvalidParamException("There is no role \"$role\".");
        }

        $auth->revoke($roleObject, $user_id);
        Yii::$app->session->addFlash('success', "Role `{$roleObject->description}` successfully revoked");
        return $this->goBack();

    }

    public function actionPermissions($user_id)
    {
        $auth = Yii::$app->authManager;
        $all = $auth->getPermissions();
        $for_user = $auth->getPermissionsByUser($user_id);
        $permissions = [];

        foreach ($all as $key => $permission)
            $permissions[$permission->description] = (array_key_exists($key, $for_user)) ? true : false;

        return $this->renderAjax('permissions', ['permissions' => $permissions]);
    }

    /**
     * @param Yii\rbac\ManagerInterface $auth
     * @param $role
     * @param $user_id
     * @throws Exception
     */
    protected function assignRole(yii\rbac\ManagerInterface $auth, $role, $user_id)
    {
        $roleObject = $auth->getRole($role);
        if (!$roleObject) {
            throw new InvalidParamException("There is no role \"$role\".");
        }

        $auth->assign($roleObject, $user_id);
        Yii::$app->session->addFlash('success', "Role `{$roleObject->description}` successfully assigned");

    }

}