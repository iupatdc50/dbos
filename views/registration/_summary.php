<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */

$heading = 'Active Projects';

?>
<div id="special-projects">

<?php
Pjax::begin(['id' => 'active-projects', 'enablePushState' => false]);
echo  GridView::widget([
		'id' => 'active-projects',
		'dataProvider' => $dataProvider,
		'floatHeader' => true,
		'panel'=>[
		        'type'=>GridView::TYPE_DEFAULT,
		        'heading'=> $heading,
		        'before' => false,
		        'after' => false,
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

