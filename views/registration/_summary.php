<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $id string license_nbr */
/* @var $awarded_only boolean */

$heading = 'Bid Projects';

?>
<div id="special-projects">

<?php
// 'id' of Pjax::begin and embedded GridView::widget must match or pagination does not work
Pjax::begin(['id' => 'bid-projects', 'enablePushState' => false]);

$show_class = $awarded_only ? 'glyphicon glyphicon-expand' : 'glyphicon glyphicon-certificate';
$show_label = $awarded_only ? 'All' : 'Awarded Only';
$toggle_awarded_only = !$awarded_only;

echo  GridView::widget([
		'id' => 'bid-projects',
		'dataProvider' => $dataProvider,
		// 'floatHeader' => true,
		'panel'=>[
		        'type'=>GridView::TYPE_DEFAULT,
		        'heading'=> $heading,
		        'after' => false,
		],
		'toolbar' => [
				'options' => ['class' => 'pull-left'],
				'content' =>
					Html::a(Html::tag('span', '', ['class' => $show_class]) . '&nbsp;Show ' . $show_label, 
							['registration/summary-json', 'id' => $id, 'awarded_only' => $toggle_awarded_only],
							['class' => 'btn btn-default'])
		],
		'columns' => [
				[
						'attribute' => 'project',
    					'format' => 'raw',
    					'value' => function($data) {
    						$label = $data->project->project_nm;
    						$type = strtolower($data->project->agreement_type);
    						return Html::a($label, ["project-{$type}/view", 'id' => $data->project_id]);
    					},
				],
				[
						'attribute' => 'type',
						'value' => 'project.agreement_type',
				],
				'bid_dt:date',
				[
						'attribute' => 'start_dt',
						'format' => 'date',
						'value' => 'isAwarded.start_dt',
				],
				[
						'attribute' => 'showPdf',
						'label' => 'Doc',
						'format' => 'raw',
						
						'value' => function($model) {
							return (isset($model->doc_id)) ?
							    Html::a(Html::beginTag('span', ['class' => 'glyphicon glyphicon-paperclip', 'title' => 'Show original agreement']),
									$model->imageUrl, ['target' => '_blank']) : '';
						},
				],
		],
]);
?>
</div>
<?php

Pjax::end();
