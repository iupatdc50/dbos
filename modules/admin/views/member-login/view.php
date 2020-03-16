<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\user\User;

/* @var $this yii\web\View */
/* @var $model app\models\member\MemberLogin */

$this->title = $model->last_nm . ', ' . $model->first_nm;
$this->params['breadcrumbs'][] = ['label' => 'Member Login Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


?>

<div class="login-record-view sixty-pct">

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
            <?= Html::a('Reset Password', ['default-pw', 'id' => $model->id], ['class' => 'btn btn-default']) ?>
        <?php endif; ?>
    </p>

    <?= /** @noinspection PhpUnhandledExceptionInspection */
    DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
            	'attribute' => 'status', 
            	'value' => Html::encode($model->getStatusText()),
            	'contentOptions' => $model->status == User::STATUS_ACTIVE ? ['class' => 'success'] : ['class' => 'danger'],
            ],
        	'id',
            [
                'attribute' => 'member_id',
                'format' => 'raw',
                'value' => Html::a($model->member_id, ['/member/view', 'id' => $model->member_id]),
            ],
            'username',
            'fullName',
//            'auth_key',
//            'password_hash',
//            'password_reset_token',
            'email:email',
            'created_at:date',
            'updated_at:date',
        	'last_login',
        ],
    ]) ?>
    

</div>

<?= $this->render('../partials/_modal') ?>