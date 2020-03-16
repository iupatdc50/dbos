<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\member\MemberLogin */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Member Login Records';
$this->params['breadcrumbs'][] = $this->title;

$auth = '<span class="glyphicon glyphicon-ok text-success"></span>';
$other = '<span class="glyphicon glyphicon-nothing text-danger"></span>';

?>
<div class="login-record-index">

    <h1><?= Html::encode($this->title) ?></h1>
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
			'before' => (Yii::$app->user->can('updateUser')) ? '' : false,
			'after' => false,
		],
		'toolbar' => ['content' => Html::a('Create Member Login', ['create'], ['class' => 'btn btn-success'])],
    	'rowOptions' => 
    			function ($model) {
    		       $css = [];
    		       $css['class'] = ($model->status == 10) ? 'default' : 'text-muted';
    		       return $css;
    			},

    	'columns' => [
            [
                    'class' => 'kartik\grid\DataColumn',
            		'attribute' => 'id',
            		'width' => '70px',
    		],
            [
            	'attribute' => 'member_id',
				'format' => 'raw',
				'value' => function($model) {
					return Html::a(Html::encode($model->member_id), '/admin/member-login/view?id=' . $model->id);
				},
            ],
            'username',
        	'last_nm',
        	'first_nm',
            // 'auth_key',
            // 'password_hash',
            // 'password_reset_token',
            // 'email:email',
/*
            [
	        		'attribute' => 'status',
	        		'width' => '140px',
            ],
            */
            // 'created_at',
            // 'updated_at',
            [
                    'attribute' => 'last_login',
                    'contentOptions' => ['style' => 'white-space: nowrap;'],
            ],
            [
            		'class' => 'yii\grid\ActionColumn',
                    'contentOptions' => ['style' => 'white-space: nowrap;'],
                    'visible' => Yii::$app->user->can('updateUser'),
			],
        ],
    ]); ?>

</div>
