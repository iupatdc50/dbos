<?php

/**
 * LMA project data entry form partial
 * 
 * On create, a single address and single registration are be entered.
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\datecontrol\DateControl;
use app\helpers\OptionHelper;


/* @var $this yii\web\View */
/* @var $model app\models\project\lma\Project */
/* @var $modelAddress app\models\project\Address */
/* @var $modelsAddress \yii\db\ActiveQuery */
/* @var $modelRegistration app\models\project\lma\Registration */
/* @var $form yii\widgets\ActiveForm */
?>

<?php
// The controller action that will render the list
$url = Url::to(['/contractor/contractor-list']);

// Script to initialize the selection based on the value of the select2 element
$initScript = <<< SCRIPT
function (element, callback) {
    var id=\$(element).val();
    if (id !== "") {
        \$.ajax("{$url}?id=" + id, {
            dataType: "json"
        }).done(function(data) { callback(data.results);});
    }
}
SCRIPT;
?>

<div class="project-form">

    <?php $form = ActiveForm::begin([
    		'options' => ['enctype' => 'multipart/form-data'], // ** Must have for file uploads **
//    		'type' => ActiveForm::TYPE_HORIZONTAL,
    		'id' => 'dynamic-form'
    ]); ?>
    
    <?php
    	$config = ['form' => $form, 'model' => $model];
    	if ($model->isNewRecord)
    		$config['address'] = $modelAddress; 
    ?>
    
    <div class="leftside fortyfive-pct">
    <?= $this->render('../project/_projectformfields', $config); ?>
<hr>
        <div class="form-group clearfix">
        	<?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    	</div>
    
    
    </div>
    
   <div class="rightside fifty-pct">
    
    <?php if($model->isNewRecord): ?>
	    <div class="panel panel-default">
	        <div class="panel-heading"><h4 class="panel-title"><i class="glyphicon glyphicon-user"></i> Registration</h4></div>
	        <div class="panel-body">
	        	<?php $config = ['form' => $form, 'model' => $modelRegistration]; ?>
	        	<?= $this->render('../registration-lma/_formfields', $config); ?>
	    	</div>
	    </div>
	<?php endif; ?>
	</div>
    
    

    <?php ActiveForm::end(); ?>

</div>

    <?php if (!$model->isNewRecord): ?>
<hr>    

    	<div class="rightside fifty-pct">
		<?= $this->render(
	    		'../partials/_addressgrid',
	    		[
	    			'modelsAddress' => $modelsAddress,
	    			'controller' => 'project-address',
	    			'relation_id' => $model->project_id,	
	    		]
	    ) ?>
	    </div>
	    
    <?= $this->render('../partials/_modal') ?>
	<?php endif; ?>
    