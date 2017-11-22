<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use kartik\datecontrol\DateControl;
use app\helpers\OptionHelper;

/* @var $this yii\web\View */
/* @var $model app\models\accounting\WaiveAssessmentForm */

// The controller action that will render the list
$url = Url::to(['/user/user-list']);

?>

<div class="waive-form">

    <?php $form = ActiveForm::begin([
    		'type' => ActiveForm::TYPE_HORIZONTAL,
    		'options' => ['class' => 'ajax-create'], // Required for modal within an update
       		'id' => 'assessment-form', 
    		'enableClientValidation' => true,
    ]); ?>

    <div class="sixty-pcnt">
    
    <?= $form->field($model, 'action_dt')->widget(DateControl::className(), [
			'type' => DateControl::FORMAT_DATE,
	])   ?>
	
	<?= $form->field($model, 'authority')->label('Authorized by')->widget(Select2::classname(), [
		'size' => Select2::SMALL,
		'pluginOptions' => [
			'allowClear' => true,
			'minimumInputLength' => 3,
			'ajax' => [
				'url' => $url,
				'dataType' => 'json',
				'data' => new JsExpression('function(params) { return {search:params.term,role:"30"}; }'),
			],
			'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
			'templateResult' => new JsExpression('function(id) { return id.text; }'),
			'templateSelection' => new JsExpression('function(id) { return id.text; }'),
		],
	]) ?>
       
    <?= $form->field($model, 'note')->textArea(['rows' => 3]) ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-success']) ?>
    </div>

    </div>
    
    <?php ActiveForm::end(); ?>

    
</div>
