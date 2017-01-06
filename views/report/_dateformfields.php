<?php

use kartik\datecontrol\DateControl;
use kartik\daterange\DateRangePicker;
use app\models\report\DateSettingsForm;

?>

<div class="date-fields">

	<?= $form->field($model, 'date_range', [
			'options'=>['class'=>'drp-container form-group'],
	])->widget(DateRangePicker::classname(), [
			'presetDropdown' => true,
			'hideInput' => true,
			'convertFormat' => true, 
			'pluginOptions' => ['locale' => [
					'separator' => DateSettingsForm::RANGE_SEPARATOR,
					'format' => 'm/d/Y',
			]],
	]); ?>
</div>

