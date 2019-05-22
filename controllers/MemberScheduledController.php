<?php


namespace app\controllers;


use app\models\member\Member;
use app\models\training\Credential;
use app\models\training\MemberScheduled;
use InvalidArgumentException;
use Yii;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class MemberScheduledController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'clear' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create', 'clear'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'clear'],
                        'roles' => ['trainingEditor'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $member_id
     * @param $catg
     * @return string|Response
     * @throws Exception
     */
    public function actionCreate($member_id, $catg)
    {
        if (($member = Member::findOne($member_id)) == null)
            throw new InvalidArgumentException('Invalid member ID passed: ' . $member_id);

        $model = new MemberScheduled([
            'member' => $member,
            'catg' => $catg,
        ]);

        if ($model->load(Yii::$app->request->post())) {
            // Because member ID is unchanged, it gets cleared on load
            if ($model->validate()) {
                $this->deleteMulti($member_id, $model->credential_id);
                if ($model->save()) {
                    Yii::$app->session->addFlash('success', "{$model->credential->credential} successfully scheduled");
                    return $this->goBack();
                }
                throw new Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
            }
        }
        return $this->renderAjax('create', compact('model'));

    }
    /**
     * @param $member_id
     * @param null $credential_id
     * @return Response
     */
    public function actionClear($member_id, $credential_id = null)
    {

        $qual = isset($credential_id) ? (Credential::findOne($credential_id)->credential) : 'member';
        $this->deleteMulti($member_id, $credential_id);

        Yii::$app->session->addFlash('success', "Schedule cleared for {$qual}");
        return $this->goBack();
    }

    protected function deleteMulti($member_id, $credential_id = null)
    {
        $condition = ['member_id' => $member_id];
        if(isset($credential_id))
            $condition['credential_id'] = $credential_id;

        return MemberScheduled::deleteAll($condition);

    }

}