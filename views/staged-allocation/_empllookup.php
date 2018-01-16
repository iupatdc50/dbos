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
$url = Url::to(['/member/member-ssn-list']);

$member_name = isset($model->member) ? $model->member->fullName : 'Search for employee...';
?>

<div class="employee-form">

    <?php $form = ActiveForm::begin([
    		'type' => ActiveForm::TYPE_HORIZONTAL,
     		'id' => 'allocation-form', 
    		'enableClientValidation' => true,
    ]); ?>

	<?= $form->field($model, 'member_id')->label('Emloyee')->widget(Select2::classname(), [
		'size' => Select2::SMALL,
		'initValueText' => $member_name,
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
        <?= Html::submitButton($label, ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

    
    
