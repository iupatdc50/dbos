<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\accounting\Receipt;

/* @var $modelReceipt \app\models\accounting\Receipt */
/* @var $this \yii\web\View */

$pt = ($modelReceipt->payor_type == Receipt::PAYOR_CONTRACTOR) ? 'contractor' : 'member';
$url = Yii::$app->urlManager->createUrl(['receipt-' . $pt. '/balances-json', 'id' => $modelReceipt->id]);
$cancel_action = ($modelReceipt->isUpdating()) ? 'cancel-update' : 'cancel-create';

?>

<div id="tallies" data-url=<?= $url ?>>
    <div id="running-display" class="pull-right">
        <div id="running-total" class="flash-success"></div>
    </div>
    <div id="balance-display" class="pull-right">
        <div id="out-of-balance" class="flash-error"></div>
    </div>
</div>

<div>
    <p>
        <?php
            if($modelReceipt->isUpdating())
                echo Html::submitButton('Post', ['id' => 'post-btn', 'class' => 'btn btn-success']);
            else
                echo Html::a('Post', ['post', 'id' => $modelReceipt->id], ['id' => 'post-btn', 'class' => 'btn btn-success']);
        ?>
        <?= Html::button('<i class="glyphicon glyphicon-check"></i>&nbsp;Balance', [
            'value' => Url::to(["balance", 'id' => $modelReceipt->id]),
            'id' => 'balance-btn',
            'class' => 'btn btn-default btn-modal',
            'data-title' => 'Adjustments',
        ]); ?>
        <?= Html::a('Cancel', [$cancel_action, 'id' => $modelReceipt->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => "Are you sure you want to proceed? You will lose the data you entered.",
                'method' => 'post',
            ],
        ]) ?>
    </p>
</div>


<?php
$script = <<< JS

$(function() {
		refreshToolBar();
});
		
JS;

$pos = \yii\web\View::POS_READY;
$this->registerJs($script, $pos);
?>
