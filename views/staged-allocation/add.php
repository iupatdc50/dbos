<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\StagedAllocation */
/* @var $license_nbr string */

// The controller action that will render the list
$url = Url::to(['/employment/employee-list']);

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="allocation-add">

<div class="allocation-form">

    <?php $form = ActiveForm::begin([
    		'type' => ActiveForm::TYPE_HORIZONTAL,
     		'id' => 'allocation-form', 
    		'enableClientValidation' => true,
    ]); ?>

	<?= $form->field($model, 'member_id')->label('Emloyee')->widget(Select2::classname(), [
		'size' => Select2::SMALL,
		'options' => ['placeholder' => 'Search for employee...'],
		'pluginOptions' => [
			'allowClear' => true,
			'minimumInputLength' => 3,
			'ajax' => [
				'url' => $url,
				'dataType' => 'json',
				'data' => new JsExpression('function(params) { return {search:params.term,employer:"'. $license_nbr .  '"}; }'),
			],
			'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
			'templateResult' => new JsExpression('function(member_id) { return member_id.text; }'),
			'templateSelection' => new JsExpression('function(member_id) { return member_id.text; }'),
		],
	]) ?>
	
    <div class="form-group">
        <?= Html::submitButton('Add', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

    
    
</div>
