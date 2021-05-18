<?php


namespace app\controllers;

use app\models\accounting\BillPayment;
use PHPExcel_Exception;
use PHPExcel_IOFactory;
use PHPExcel_Reader_Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;

class BillPaymentController extends Controller
{
    public function actionSummaryJson()
    {
        if (isset($_POST['expandRowKey'])) {

            $query = BillPayment::find()
                ->where(['bill_id' => $_POST['expandRowKey']])
                ->joinWith('receipt')
                ->orderBy('receipt_id desc');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 5],
                'sort' => false,
            ]);

            return $this->asJson($this->renderAjax('_summary', [
                'dataProvider' => $dataProvider,
            ]));
        }
        Yii::$app->session->addFlash('error', 'No Billing row selected [Error: BPC010]');
        return $this->goBack();
    }

    /**
     * @param $xlsx_name
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     */
    public function actionShowTransmittal($xlsx_name)
    {
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
        header("Content-Type: application/force-download");
        header("Content-Type: application/xlsx; charset=utf-8");
        header("Content-Disposition: attachment; filename=$xlsx_name");
        header("Expires: 0");
        $path = Yii::getAlias('@webroot') . Yii::$app->params['uploadDir'];
        $objPHPExcel = PHPExcel_IOFactory::load($path . $xlsx_name);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}