<?php

use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\widgets\Select2;

/* @var $className string */

?>

<div class="pull-right">
	<?= Select2::widget([
		'name' => 'quicksearch',
		'size' => Select2::SMALL,
		'options' => [
				'placeholder' => 'Quick search',
		],
		'pluginOptions' => [
				'width' => 350,
				'minimumInputLength' => 3,
				'ajax' => [
						'url' => Url::to(["/{$className}/{$className}-list"]),
						'dataType' => 'json',
						'data' => new JsExpression('function(params) { return {search:params.term}; }'),
				],
		],
		'pluginEvents' => [
				'select2:select' => 'function(e) { $(location).attr("href", "/' . $className . '/view?id=" + e.params.data.id);}',
		],
	]) ?>
</div>
