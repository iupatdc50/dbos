<?php

use app\models\contractor\Contractor;
use yii\data\ActiveDataProvider;
use yii\widgets\ListView;

/* @var $dataProvider ActiveDataProvider */
/* @var $contractorModel Contractor */

?>

<h4 class="sm-print">9a Cards for: <?= $contractorModel->license_nbr . ' ' . $contractorModel->contractor ?></h4>

<?= /** @noinspection PhpUnhandledExceptionInspection */
ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '_document',
]); ?>

