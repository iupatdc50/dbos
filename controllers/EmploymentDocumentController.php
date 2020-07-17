<?php


namespace app\controllers;


use app\models\employment\Document;
use app\models\employment\Employment;
use InvalidArgumentException;
use Throwable;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class EmploymentDocumentController extends Controller
{
    public function actionSummaryJson()
    {
        if (isset($_POST['expandRowKey'])) {

            $keys = $_POST['expandRowKey'];

            $query = Document::find()
                ->where(['member_id' => $keys['member_id'], 'effective_dt' => $keys['effective_dt']])
                ->orderBy('doc_type asc');
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 5],
                'sort' => false,
            ]);

            return $this->asJson($this->renderAjax('_summary', [
                'dataProvider' => $dataProvider,
                'member_id' => $keys['member_id'],
                'effective_dt' => $keys['effective_dt'],
            ]));
        }
        Yii::$app->session->addFlash('error', 'No Employment row selected [Error: EDC010]');
        return $this->goBack();
    }

    public function actionCreate($member_id, $effective_dt)
    {
        if (($employment = Employment::findOne(['member_id' => $member_id, 'effective_dt' => $effective_dt])) == null)
            throw new InvalidArgumentException("Invalid keys passed (member_id: {$member_id}, effective_dt: {$effective_dt}");

        $model = new Document(['employment' => $employment]);

        if ($model->load(Yii::$app->request->post())) {
            // Prepopulate referencing column
            $image = $model->uploadImage();
            if	($model->save()) {
                if ($image !== false) {
                    $path = $model->imagePath;
                    $image->saveAs($path);
                }
                return $this->goBack();
            }
        }

        return $this->renderAjax('create', compact('model'));
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = Document::findOne($id);
        if (!$model)
            throw new NotFoundHttpException('The requested page does not exist');
        if ($model->delete()) {
            if (!$model->deleteImage())
                Yii::$app->session->setFlash('error', 'Could not delete document');
        }
        return $this->goBack();
    }

}