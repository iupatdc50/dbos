<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\user\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'User Records';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-record-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
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
		'toolbar' => ['content' => Html::a('Create User', ['create'], ['class' => 'btn btn-success'])],
    	'columns' => [
            [
            		'attribute' => 'id',
            		'width' => '70px',
    		],
            'username',
        	'last_nm',
        	'first_nm',
            // 'auth_key',
            // 'password_hash',
            // 'password_reset_token',
            // 'email:email',
            [
            		'attribute' => 'role',
            		'width' => '140px',
            ],
	        [
	        		'attribute' => 'status',
	        		'width' => '140px',
            ],
            // 'created_at',
            // 'updated_at',
            'last_login',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
