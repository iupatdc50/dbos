<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

?>

<?= DetailView::widget([
		'model'  => $duesProvider,
		'options' => ['class' => 'table table-striped table-bordered detail-view op-dv-table text-left'],
		'attributes' => [
				'months',
				'paid_thru_dt:date',
		],
]);
?>