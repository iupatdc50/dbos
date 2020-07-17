<?php /** @noinspection PhpUnhandledExceptionInspection */

use yii\widgets\DetailView;
use kartik\widgets\FileInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\member\Status */
/* @var $form yii\widgets\ActiveForm */

$this->title = "Update Status for {$model->member->fullName}";
$this->params['breadcrumbs'][] = ['label' => 'Members', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->member->fullName, 'url' => ['view', 'id' => $model->member_id]];
$this->params['breadcrumbs'][] = 'Update';
?>


<div class="memberstatus-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="leftside forty-pct">

        <?= DetailView::widget([
                'model' => $model,
                'options' => ['class' => 'table table-striped table-bordered detail-view op-dv-table-sm text-left'],
                'attributes' => [
                    [
                        'label' => 'Trade',
                        'value' => Html::encode($model->lob->short_descrip),
                    ],
                    'effective_dt:date',
                    'end_dt:date',
                    [
                        'attribute' => 'member_status',
                        'value' => Html::encode($model->status->descrip),
                    ],
                ],
        ]); ?>

    </div>

    <div class="rightside fiftyfive-pct">

        <?php $form = ActiveForm::begin([
            //   		'options' => ['class' => 'ajax-_form'], // Required for modal within an update
            'id' => 'status-update',
            'enableClientValidation' => true,

        ]); ?>

        <?= $form->field($model, 'reason')->textarea(['rows' => 6]) ?>

        <?= $form->field($model, "doc_file")->widget(FileInput::className(), [
            'options' => ['accept' => 'application/pdf'],
            'pluginOptions'=> [
                'allowedFileExtensions'=>['pdf','png'],
                'showUpload' => false,
            ],
        ]); ?>

        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>


</div>
