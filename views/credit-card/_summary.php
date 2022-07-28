<?php

/* @var $this yii\web\View */
/* @var $card Stripe\Card */
/* @var $plan Stripe\Plan */
/* @var $product Stripe\Product */
/* @var $stripe_subs Stripe\Subscription */
/* @var $member_id string */

use app\helpers\OptionHelper;
use Stripe\Subscription;
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
                            [
                                'attribute' => 'status',
                                'visible' => $stripe_subs->status <> Subscription::STATUS_CANCELED,
                            ],
                            [
                                'label' => 'Next Chg',
                                'format' => 'raw',
                                'value' => function (Subscription $model) {
                                    return ($model->status == Subscription::STATUS_PAST_DUE) ? 'pending' : date('m/d/Y', $model->current_period_end);
                                },
                                'visible' => $stripe_subs->status <> Subscription::STATUS_CANCELED,
                            ],
                            [
                                'label' => 'Canceled',
                                'format' => 'raw',
                                'value' => function (Subscription $model) {
                                    return date('m/d/Y', $model->canceled_at);
                                },
                                'visible' => $stripe_subs->status == Subscription::STATUS_CANCELED,
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
                    <?php if($stripe_subs->status == Subscription::STATUS_CANCELED): ?>
                        <?= Html::button('<i class="glyphicon glyphicon-edit"></i>&nbsp;Re-Enroll Subscription',
                            ['value' => Url::to(['enroll', 'id'  => $member_id]),
                                'id' => 'enrollButton',
                                'class' => 'btn btn-default btn-modal',
                                'data-title' => 'Auto-Pay Enrollment',
                            ])
                        ?>
                    <?php else: ?>
                        <?=  Html::a('<i class="glyphicon glyphicon-remove"></i> Cancel Subscription', ['cancel-subscription', 'id' => $member_id], [
                                'class' => 'btn btn-default',
                                'data' => [
                                    'confirm' => 'Are you sure you want to cancel this subscription?',
                                    'method' => 'post',
                                ],

                        ]);
                        ?>
                    <?php endif; ?>
                </div>
            </div>
        </td>
    </tr>
</table>
