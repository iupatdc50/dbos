<?php /** @noinspection PhpUnhandledExceptionInspection */

/**
 * Member data entry form partial
 *
 * On create, a single address and single phone may be entered.
 */

use kartik\widgets\Select2;
use kartik\checkbox\CheckboxX;
use kartik\datecontrol\DateControl;


/* @var $this yii\web\View */
/* @var $model app\models\member\Member */
/* @var $modelAddress app\models\member\Address */
/* @var $modelPhone app\models\member\Phone */
/* @var $modelEmail app\models\member\Email */
/* @var $modelStatus app\models\member\Status */
/* @var $modelClass app\models\member\MemberClass */
/* @var $form yii\widgets\ActiveForm */
?>

    	<hr>
    	<?= $this->render('../partials/_addressformfields',
    			[
    				'form'	=> $form,
    				'address' => $modelAddress,
    			]
    	) ?>
    	<hr>
    	<?= $this->render('../partials/_phoneformfields',
    			[
    				'form'	=> $form,
    				'phone' => $modelPhone,
    			]
    	) ?>
    	<hr>
    	<?= $form->field($modelEmail, 'email')->textInput(['maxlength' => 50]) ?>

    	<?= $form->field($modelStatus, 'lob_cd')->widget(Select2::className(), [
    		'data' => $modelStatus->lobOptions,
    		'hideSearch' => false,
    		'size' => Select2::SMALL,
    		'options' => ['placeholder' => 'Select...'],
    	]); ?>
    	
    	<?= $this->render('../member-class/_formfields', [
    			'form' => $form,
    			'modelClass' => $modelClass,
    	]) ?>
    	    	
    	<hr>
    	
	    <?= $form->field($model, 'application_dt')->widget(DateControl::className(), [
	    		'type' => DateControl::FORMAT_DATE,
	    ])->label('Appl Date') ?>

    	
    	<?= $form->field($model, 'exempt_apf')->widget(CheckboxX::className(), ['pluginOptions' => ['threeState' => false]]); ?>

        <?= $form->field($model, 'is_ccd')->widget(CheckboxX::className(), [
                'pluginOptions' => ['threeState' => false],
                'pluginEvents' => [
                    'change' => "function () {
    					if ($(this).val() == '1') {
    						\$('#ccdfields').show();
						} else {
    						\$('#ccdfields').hide();
						}
					}",
                ],
        ]); ?>

        <div hidden id="ccdfields">
            <?= $form->field($modelStatus, 'other_local')->textInput(['maxlength' => 10])->label('Previous Local') ?>
            <?= $form->field($model, 'init_dt')->widget(DateControl::className(), [
                'type' => DateControl::FORMAT_DATE,
            ])->label('Init Date') ?>
        </div>

