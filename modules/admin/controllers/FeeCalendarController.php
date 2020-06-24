<?php


namespace app\modules\admin\controllers;

use app\helpers\TokenHelper;
use app\models\accounting\FeeCalendar;
use Yii;
use yii\db\Exception;
use yii\web\Controller;


class FeeCalendarController extends Controller
{
    /**
     * Repopulates the fee calendar table used to determine
     * dues balance owed
     */
    public function actionRefresh()
    {
        $db = Yii::$app->db;
        try {
            FeeCalendar::deleteAll();
            $years = FeeCalendar::REFRESH_YEARS;
            $db->createCommand("CALL PopulateFeeCalendar (:years)", [':years' => $years])->execute();
            TokenHelper::removeToken(FeeCalendar::TOKEN_REFRESH);
            Yii::$app->session->addFlash('success', "Fee Calendar refreshed; projected forward {$years} years from latest effective date");
        } catch (Exception $e) {
            Yii::error('*** FC010: Unable populate fee calendar. Error(s) ' . print_r ($e->errorInfo, true));
            Yii::$app->session->addFlash('error', 'Problem with refresh. Please contact support immediately.  Error: `FC010`');
        }
        $this->goBack();
    }
}