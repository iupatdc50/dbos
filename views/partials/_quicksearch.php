<?php

use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\widgets\Select2;

/**
 * _quicksearch Select2 partial used to move quickly between one view item and
 * another without having to return to index
 * 
 * After 3 characters, uses the 'url' to build a shortlist
 * 
 * [Model]Controller must have an action in the format action[Model]List
 * -- where [Model] is name of the model from where the shortlist is built
 * 
 * The select event of this control routes to the view action of [ModelController]
 * passing the id value of associated with the shortlist item
 * 
 */

/* @var $className string */

?>

<div class="qsearch pull-right">
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
