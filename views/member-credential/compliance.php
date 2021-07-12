<?php

use app\models\member\ClassCode;
use app\models\training\CredCategory;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $member app\models\member\Member */
/* @var $recurProvider yii\data\ActiveDataProvider */
/* @var $nonrecurProvider yii\data\ActiveDataProvider */
/* @var $medtestsProvider yii\data\ActiveDataProvider */
/* @var $coreProvider yii\data\ActiveDataProvider */

$this->title = 'Compliance for ' . $member->fullName;

$this->params['breadcrumbs'][] = ['label' => 'Members', 'url' => ['/member/index']];
$this->params['breadcrumbs'][] = ['label' => $member->fullName, 'url' => ['/member/view', 'id' => $member->member_id]];
$this->params['breadcrumbs'][] = 'Compliance';

?>
<div class="compliance-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php
    if (Yii::$app->user->can('manageTraining'))
        echo Html::a(
            '<i class="glyphicon glyphicon-export"></i>&nbsp;Certificate',
            ['/report/certificate', 'member_id' => $member->member_id],
            ['class' => 'btn btn-default']
        );
        if (in_array($member->currentClass->member_class, [ClassCode::CLASS_APPRENTICE, ClassCode::CLASS_HANDLER]))
            echo Html::a(
                '<i class="glyphicon glyphicon-export"></i>&nbsp;Transfer Form',
                ['/report/transfer-form', 'member_id' => $member->member_id],
                ['class' => 'btn btn-default']
            );
    ?>
    <hr>

    <div class="leftside fifty-pct">

        <?=
        $this->render('_summary', [
            'dataProvider' => $recurProvider,
            'relation_id' => $member->member_id,
            'heading' => 'Recurring',
            'doc' => true,
            'expires' => true,
            'catg' => CredCategory::CATG_RECURRING,
        ]); ?>

        <hr>

        <?=
        $this->render('_summary', [
            'dataProvider' => $nonrecurProvider,
            'relation_id' => $member->member_id,
            'heading' => 'Non-expiring',
            'doc' => true,
            'expires' => false,
            'catg' => CredCategory::CATG_NONRECUR,
        ]); ?>

    </div><div class="rightside fortyfive-pct">

        <?=
        $this->render('_summary', [
            'dataProvider' => $medtestsProvider,
            'relation_id' => $member->member_id,
            'heading' => 'Medical Tests',
            'doc' => true,
            'expires' => true,
            'catg' => CredCategory::CATG_MEDTESTS,
        ]); ?>

        <hr>

        <?=
        $this->render('_summary', [
            'dataProvider' => $coreProvider,
            'relation_id' => $member->member_id,
            'heading' => 'Apprenticeship School',
            'doc' => false,
            'expires' => false,
            'catg' => CredCategory::CATG_CORE,
        ]); ?>

    </div>

</div>

<?= $this->render('../partials/_modal') ?>
