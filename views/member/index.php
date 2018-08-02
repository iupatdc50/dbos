<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\helpers\CriteriaHelper;
use app\models\member\Status;

/* @var $this yii\web\View */
/* @var $searchModel app\models\member\MemberSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $statusPickList array */

$this->title = 'Members';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    
    <?= /** @noinspection PhpUnhandledExceptionInspection */
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
 		'filterRowOptions'=>['class'=>'filter-row'],
		'panel'=>[
	        'type'=>GridView::TYPE_PRIMARY,
	        'heading'=> $this->title,
			// workaround to prevent 1 in the before section
			'before' => (Yii::$app->user->can('createMember')) ? '' : false,
			'after' => false,
		],
		'toolbar' => ['content' => Html::a('Create Member', ['create'], ['class' => 'btn btn-success'])],
		'rowOptions' => function($model) {
							$css = ['verticalAlign' => 'middle'];
        					if(!isset($model->currentStatus) || ($model->currentStatus->member_status == 'I')) 
								$css['class'] = 'text-muted';
        					else 
        						$css['class'] = $model->isPastGracePeriodNotDropped() ? 'danger' : ($model->isDelinquentNotSuspended() ? 'warning' : 'default'); 
        					return $css;
    					},
        'columns' => [

    		[
    			'attribute' => 'lob_cd',
    			'value' => 'currentStatus.lob_cd',
    			'width' => '80px',
    			'label' => 'Local',
    		],
    		
    		[
				'attribute' => 'status',
    			'width' => '140px',
    			'value' => 'currentStatus.status.descrip',
            	'filterType' => GridView::FILTER_SELECT2,
            	'filter' => array_merge([CriteriaHelper::TOKEN_NOTSET => "(not set)"], $statusPicklist),
            	'filterWidgetOptions' => [
            			'size' => \kartik\widgets\Select2::SMALL,
            			'hideSearch' => true,
            			'pluginOptions' => ['allowClear' => true, 'placeholder' => 'All'],
            	],
    		],
        	[
        		'attribute' => 'class',
        		'value' => 'currentClass.mClassDescrip',
        		'label' => 'Class',
        	],
			'report_id',
            [
            	'attribute' => 'fullName',
            	'contentOptions' => ['style' => 'white-space: nowrap;'],
				'format' => 'raw',
				'value' => function($model) {
					return Html::a(Html::encode($model->fullName), '/member/view?id=' . $model->member_id);
				},
            ],
        	[
            		'attribute' => 'specialties',
        			'value' => 'specialtyTexts',
        			'format' => 'ntext',
        			'contentOptions' => ['style' => 'white-space: nowrap;'],
        	],
        	[
        		'attribute' => 'dues_paid_thru_dt',
        		'format' => 'date',
        		'label' => 'Paid Thru',
                'value' => function($model) {
                    return (isset($model->currentStatus) && ($model->currentStatus->member_status == Status::OUTOFSTATE))
                        ? null : $model->dues_paid_thru_dt;
                },
                'visible' => Yii::$app->user->can('browseMemberExt'),
        	],
        		
        	[
        			'attribute' => 'employer', 
        			'value' => function($model) {
                        return isset($model->employer->descrip) ? $model->employer->descrip : 'Unemployed';
                    },
        			'contentOptions' => ['style' => 'white-space: nowrap;'],
        	],
            [
            	'class' => 'yii\grid\ActionColumn',
            	'contentOptions' => ['style' => 'white-space: nowrap;'],
            	'visible' => Yii::$app->user->can('updateMember'),
            ],
        ],
    ]); ?>

</div>
