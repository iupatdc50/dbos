<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

/* @var $modelsPhone \yii\db\ActiveQuery */
/* @var $controller string */
/* @var $relation_id string */

$default = '<span class="glyphicon glyphicon-ok text-success"></span>';
$other = '<span class="glyphicon glyphicon-nothing text-danger"></span>';


?>
        <div class="form-group">
				<?= GridView::widget([
			        	'dataProvider' => new \yii\data\ActiveDataProvider([
			        		'query' => $modelsPhone,
			        		'pagination' => false,
			        	]),
						'summary' => '',
						'panel'=>[
					        'type'=>GridView::TYPE_DEFAULT,
					        'heading'=> '<i class="glyphicon glyphicon-phone-alt"></i>&nbsp;Phones',
					        'before' => false,
					        'after' => false,
					        'footer' => false,
	   					],
						'columns' => [
							[
									'class' => 'kartik\grid\BooleanColumn',
									'attribute' => 'default',
									'value' => function($data) {
										return isset($data->isDefault);
									},
									'falseIcon' => $other,
									'trueIcon' => $default,
							],
			        		[
			        				'attribute' => 'phone_type', 
			        				'value' => 'phoneType.descrip',
			        		],
			        		'phone',
			        		'ext',
				        	[
				                'class' => \yii\grid\ActionColumn::className(),
				                'controller' => $controller,
				                'template' => '{update}{delete}',
				            	'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add', 
				            			['value' => Url::to(["/{$controller}/create", 'relation_id'  => $relation_id]), 
				            				'id' => 'phoneCreateButton',
				            				'class' => 'btn btn-default btn-modal btn-embedded',
				            				'data-title' => 'Phone',	
				    					]),
				            ],
			        	],
			    ]);?>
	    </div>
			    