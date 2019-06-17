<?php

use kartik\editable\Editable;
use kartik\grid\GridView;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $modelTimesheet app\models\training\Timesheet */
/* @var $hoursProvider ActiveDataProvider */

?>



<div>

    <?= /** @noinspection PhpUnhandledExceptionInspection */
    GridView::widget([
        'id' => 'hours-grid',
        'dataProvider' => $hoursProvider,
        'pjax' => true,
        'panel'=>[
            'type'=>GridView::TYPE_DEFAULT,
            'heading'=>'Hours',
            'before' => false,
            'after' => false,
            'footer' => false,
        ],
        'showPageSummary' => true,
        'pageSummaryRowOptions' => ['class' => 'kv-page-summary default'],
        'columns' => [
            [
                'attribute' => 'wp_seq',
                'class' => 'kartik\grid\EditableColumn',
                'editableOptions' => [
                    'formOptions' => ['action' => '/work-hour/edit'],
                    'showButtons' => false,
                    'buttonsTemplate' => '{submit}',
                    'asPopover' => false,
                    'inputType' => Editable::INPUT_SELECT2,
                    'options' => [
                        'size' => Select2::SMALL,
                        'data' => $modelTimesheet->unusedProcesses,
                        'options' => ['placeholder' => 'Select process...'],
                        'hideSearch' => true,
                    ],
                ],
                'value' => function($model) {
                    return $model->workProcess->descrip;
                },
                'label' => 'Process',
            ],
            [
                'attribute' => 'hours',
                'class' => 'kartik\grid\EditableColumn',
                'editableOptions' => [
                    'formOptions' => ['action' => '/work-hour/edit'],
                    'showButtons' => false,
                    'buttonsTemplate' => '{submit}',
                    'asPopover' => false,
                ],
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 2],
                'pageSummary' => true,
                'refreshGrid' => true,
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                            'title' => Yii::t('app', 'Remove'),
                            'data-confirm' => 'Are you sure you want to delete this item?',
                        ]);
                    }
                ],
                'urlCreator' => function ($action, $model) {
                    if ($action === 'delete') {
                        $url = '/work-hour/delete?id=' . $model->id;
                        return $url;
                    }
                    return null;
                },
                'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add',
                    [
                        'value' => Url::to(["/work-hour/create", 'timesheet_id' => $modelTimesheet->id]),
                        'id' => 'hoursCreateButton',
                        'class' => 'btn btn-default btn-modal btn-embedded',
                        'data-title' => 'Hours',
                    ]),

            ],

        ]

    ])
    ?>

</div>

<hr>

<div class="form-fields">

    <?php $form = ActiveForm::begin([
        'layout' => 'horizontal',
        'id' => 'ts-update',
        'enableClientValidation' => true,
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'label' => 'col-md-4',
                'wrapper' => 'col-md-7',
            ],
        ],
    ]); ?>

    <?php if(array_key_exists($modelTimesheet->acct_month, $modelTimesheet->acctMonthOptions)): ?>
        <?= $form->field($modelTimesheet, 'acct_month')->widget(Select2::className(), [
            'size' => Select2::SMALL,
            'data' => $modelTimesheet->acctMonthOptions,
            'options' => ['placeholder' => 'Select month...'],
        ]) ?>
    <?php else: ?>
        <div class="form-group total">
            <label class="control-label col-sm-4" style="margin-right: 15px" for="acct_month">Account Month</label>
            <div id="acct_month" class="col-sm-4 flash-notice"><?= $modelTimesheet->acctMonthText ?></div>
        </div>
    <?php endif; ?>


    <?= $form->field($modelTimesheet, 'remarks')->textarea(['rows' => 3]) ?>

    <hr>

    <?= Html::submitButton('Update', ['id' => 'updatebtn', 'class' => 'btn btn-success']) ?>

    <?php ActiveForm::end(); ?>



</div>

<?php

$script = <<< JS

$(document).keydown(function(e) {

  // Set self as the current item in focus
  var self = $(':focus'),
      form = self.parents('form:eq(0)'),
      next,
      focusable;

  focusable = $('div.timesheet-formfields').find('input,a,select,button,textarea').filter(':visible');

  if (e.which === 13) {
      next = focusable.eq(focusable.index(self) + 1);
      if (next.length) {
          next.focus();
      } else {
          form.submit();
      }
      return false;
  }
});

JS;
$this->registerJs($script);

?>

