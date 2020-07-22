<?php

namespace app\controllers;

use app\controllers\basedoc\SubmodelController;
use app\models\contractor\CurrentSignatoryAgreement;
use app\models\contractor\HistorySignatoryAgreement;
use app\models\contractor\Signatory;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * ContractorAddressController implements the CRUD actions for ContractorAddress model.
 */
class ContractorSignatoryController extends SubmodelController
{
	public $recordClass = 'app\models\contractor\Signatory';
    public $relationAttribute = 'license_nbr';

	public function actionSummaryJson($id)
    {
        $query = CurrentSignatoryAgreement::find()
            ->where(['license_nbr' => $id])
            ->orderBy(['lob_cd' => SORT_ASC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 5],
            'sort' => false,
        ]);

        return $this->asJson($this->renderAjax('_summary', ['dataProvider' => $dataProvider, 'id' => $id]));

    }

	public function actionHistoryJson()
    {
        if (isset($_POST['expandRowKey'])) {

            $current = Signatory::findOne($_POST['expandRowKey']);

            $query = HistorySignatoryAgreement::find()
                ->where(['lob_cd' => $current->lob_cd, 'license_nbr' => $current->license_nbr])
                ->orderBy(['signed_dt' => SORT_DESC]);
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 5],
                'sort' => false,
            ]);
            return $this->asJson($this->renderAjax('_history', [
                'dataProvider' => $dataProvider,
            ]));
        }

        Yii::$app->session->addFlash('error', 'No Signatory row selected [Error: CSC010]');
        return $this->goBack();

    }
}
