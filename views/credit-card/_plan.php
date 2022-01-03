<?php

use yii\widgets\DetailView;
use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $price Stripe\Price */

?>

<div>
    <input type="hidden" name="price_id" value="<?= $price->id; ?>">
    <?=
    /** @noinspection PhpUnhandledExceptionInspection */
    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    DetailView::widget([
            'model' => $price,
            'options' => ['class' => 'table table-cc-existing table-bordered ninety-pct op-dv-table'],
            'attributes' => [
                    [
                        'label' => 'Item',
                        'value' => Html::encode($price->product->name),
                    ],
                    'currency',
                    [
                        'label' => 'Amount',
                        'value' => Html::encode($price->unit_amount_decimal / 100),
                        'format' => ['decimal', 2],
                    ],
                    [
                        'label' => 'Per',
                        'value' => Html::encode($price->recurring->interval),
                    ],
            ],
        ]);
    ?>
</div>


