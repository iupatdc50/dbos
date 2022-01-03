<?php

/* @var $this yii\web\View */
/* @var $card Stripe\Card */
/* @var $plan Stripe\Plan */
/* @var $product Stripe\Product */
/* @var $stripe_subs Stripe\Subscription */
/* @var $member_id string */

use app\helpers\OptionHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;


?>

<table id="subs-summary" class="hundred-pct">
    <tr class="datatop">
        <td class="fifty-pct pad-six">
            <div class="panel panel-default">
                <div class="panel-heading"><h4 class="panel-title">Payment Info</h4></div>
                <div class="panel-body">
                    <?= /** @noinspection PhpUnhandledExceptionInspection */
                        DetailView::widget([
                            'model' => $card,
                            'options' => ['class' => 'table table-bordered detail-view op-dv-table'],
                            'attributes' => [
                                [
                                        'label' => 'Method',
                                        'value' => Html::img(Yii::$app->params['logoDir'] . OptionHelper::getBrandLogoNm($card->brand), ['class' => 'cc-logo']) . ' ' . $card->brand . ': ' . OptionHelper::getBrandMask($card->brand) . $card->last4 ,
                                        'format' => 'raw',
                                ],
                                [
                                        'label' => 'Expires',
                                        'value' => Html::encode($card->exp_month . '/' . $card->exp_year),
                                ],
                            ],
                        ]);
                    ?>
                    <?= Html::button('<i class="glyphicon glyphicon-edit"></i>&nbsp;Update',
                        ['value' => Url::to(['update-card', 'id'  => $member_id]),
                            'id' => 'updateButton',
                            'class' => 'btn btn-default btn-modal',
                            'data-title' => 'Expire Date',
                        ])
                    ?>
                </div>
            </div>
        </td><td class="pad-six">
            <div class="panel panel-default">
                <div class="panel-heading"><h4 class="panel-title">Auto Pay Plan</h4></div>
                <div class="panel-body">
                    <?= /** @noinspection PhpUnhandledExceptionInspection */
                    DetailView::widget([
                        'model' => $stripe_subs,
                        'options' => ['class' => 'table table-striped table-bordered detail-view op-dv-table'],
                        'attributes' => [
                            [
                                'label' => 'Item',
                                'value' => Html::encode($product->name),
                            ],
                            'status',
                            [
                                'label' => 'Next Chg',
                                'value' => date('m/d/Y', $stripe_subs->billing_cycle_anchor),
                            ],
                            [
                                'label' => 'Currency',
                                'value' => Html::encode($plan->currency),
                            ],
                            [
                                'label' => 'Amount',
                                'value' => Html::encode($plan->amount / 100),
                                'format' => ['decimal', 2],
                            ],
                            [
                                'label' => 'Per',
                                'value' => Html::encode($plan->interval),
                            ],
                        ],
                    ]);
                    ?>
                    <?=  Html::a('<i class="glyphicon glyphicon-remove"></i> Cancel Subscription', ['cancel-subscription', 'id' => $member_id], [
                            'class' => 'btn btn-default',
                            'data' => [
                                'confirm' => 'Are you sure you want to cancel this subscription?',
                                'method' => 'post',
                            ],

                    ]);
                    ?>
                </div>
            </div>
        </td>
    </tr>
</table>
