<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use app\models\user\User;

/* @var $this yii\web\View */
/* @var $model app\models\user\User */
/* @var $rolesModel \yii\data\ActiveDataProvider */

$this->title = $model->last_nm . ', ' . $model->first_nm;
$this->params['breadcrumbs'][] = ['label' => 'User Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


?>
<div class="user-record-view forty-pct">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
       	<?php if(Yii::$app->user->can('updateUser')): ?>
    	<?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?php endif; ?>
        <?php if(Yii::$app->user->can('assignRole')): ?>
    	<?= Html::a('Reset Password', ['default-pw', 'id' => $model->id], ['class' => 'btn btn-default']) ?>
        <?php endif; ?>
    </p>

    <?= /** @noinspection PhpUnhandledExceptionInspection */
    DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
            	'attribute' => 'status', 
            	'value' => Html::encode($model->statusText),
            	'contentOptions' => $model->status == User::STATUS_ACTIVE ? ['class' => 'success'] : ['class' => 'danger'],
            ],
        	'id',
            'username',
//            'auth_key',
//            'password_hash',
//            'password_reset_token',
            'email:email',
            'role',
            'created_at:date',
            'updated_at:date',
        	'last_login',
        ],
    ]) ?>
    
    <?= /** @noinspection PhpUnhandledExceptionInspection */
    GridView::widget([
    		'dataProvider' => $rolesModel,
			'panel'=>[
					'type'=>GridView::TYPE_DEFAULT,
					'heading'=>'<i class="glyphicon glyphicon-check"></i>&nbsp;Role Assignments',
					'before' => false,
					'after' => false,
					'footer' => false,
			],
			'columns' => [
					[
							'class'=>'kartik\grid\ExpandRowColumn',
							'width'=>'50px',
							'value'=>function (/** @noinspection PhpUnusedParameterInspection */
                                $model, $key, $index, $column) {
										return GridView::ROW_COLLAPSED;
									 },
							'detailUrl'=> Yii::$app->urlManager->createUrl(['role/summary-ajax']),
							'headerOptions'=>['class'=>'kartik-sheet-style'],
    						'expandOneOnly'=>true,
					],
					[
							'attribute' => 'item_name',
							'value' => 'itemName.description',
   					],
					[
							'class' => 	'kartik\grid\ActionColumn',
							'template' => '{revoke}',
                            'buttons' => [
                                'revoke' => function ($url, $model) {
                                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                                        'title' => Yii::t('app', 'Revoke'),
                                        'data-confirm' => 'Are you sure you want to revoke this role?',
                                    ]);
                                }
                            ],
							'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add',
									[
											'value' => Url::to(["role/add", 'user_id'  => $model->id]),
											'id' => 'roleAddButton',
											'class' => 'btn btn-default btn-modal btn-embedded',
											'data-title' => 'Role',
							]),
                            'urlCreator' => function ($action, $model, $key, $index) {
                                if ($action === 'revoke') {
                                    $url ='/role/revoke?role=' .$model->item_name . '&user_id=' . $model->user_id;
                                    return $url;
                                }
                            }

					],
			],
    ]); ?>

</div>

<?= $this->render('../partials/_modal') ?>