<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

/* @var $modelsAddress \yii\db\ActiveQuery */
/* @var $controller string */
/* @var $relation_id string */
?>

        <div class="form-group">
				<?= GridView::widget([
			        'dataProvider' => new \yii\data\ActiveDataProvider([
			       		'query' => $modelsAddress,
			       		'pagination' => false,
			       	]),
					'panel'=>[
				        'type'=>GridView::TYPE_DEFAULT,
				        'heading'=> '<i class="glyphicon glyphicon-envelope"></i>&nbsp;Addresses',
				        'before' => false,
				        'after' => false,
				        'footer' => false,
   					],
					'summary' => '',
					'columns' => [
							['attribute' => 'address_type', 'value' => 'typeText'],
							[
									'label' => 'Address',
									'value' => 'addressText',
							],
							[
									'class' => \yii\grid\ActionColumn::className(),
									'controller' => $controller,
									'template' => '{update}{delete}',
									'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add',
											['value' => Url::to(["/{$controller}/create", 'relation_id'  => $relation_id]),
													'id' => 'addressCreateButton',
													'class' => 'btn btn-default btn-modal btn-embedded',
													'data-title' => 'Address',
											]),
							],
					],
				]);?>
	    </div>
			