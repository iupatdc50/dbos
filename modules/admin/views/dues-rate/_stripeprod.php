<?php

use app\models\accounting\DuesStripeProduct;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $id int DuesRate `id` */
/* @var $product DuesStripeProduct */

?>

<div class="fifty-pct">

    <?php if(isset($product)): ?>
        <div class="panel panel-default"></div>
        <div class="panel-heading"><h4 class="panel-title"><i class="glyphicon glyphicon-menu-hamburger"></i> Stripe Product</h4></div>
        <div class="panel-body">

            <?= /** @noinspection PhpUnhandledExceptionInspection */

                DetailView::widget([
                    'model' => $product,
                    'attributes' => [
                        'stripe_id',
                        'stripe_price_id',
                    ],
                ]);
            ?>
            <?= Html::a('Delete', ['delete-product', 'id' => $id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]) ?>

        </div>
    <?php else: ?>
        <h5>No Stripe Product</h5>
        <?= Html::a('Create Product', ['create-product', 'id' => $id], ['class' => 'btn btn-success']) ?>
    <?php endif; ?>

</div>
