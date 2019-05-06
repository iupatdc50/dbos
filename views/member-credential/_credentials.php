<?php

use app\models\training\CredCategory;
use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $member app\models\member\Member */
/* @var $recurProvider yii\data\ActiveDataProvider */
/* @var $nonrecurProvider yii\data\ActiveDataProvider */
/* @var $medtestsProvider yii\data\ActiveDataProvider */
/* @var $coreProvider yii\data\ActiveDataProvider */


/** @noinspection PhpUnhandledExceptionInspection */
echo Tabs::widget([
    'items' => [
        [
            'label' => 'Recurring',
            'content' => $this->render('_summary', [
                'dataProvider' => $recurProvider,
                'relation_id' => $member->member_id,
                'heading' => false,
                'expires' => true,
                'catg' => CredCategory::CATG_RECURRING,
            ]),
        ],
        [
            'label' => 'Non-expiring',
            'content' => $this->render('_summary', [
                'dataProvider' => $nonrecurProvider,
                'relation_id' => $member->member_id,
                'heading' => false,
                'expires' => false,
                'catg' => CredCategory::CATG_NONRECUR,
            ]),
        ],
        [
            'label' => 'Medical Tests',
            'content' => $this->render('_summary', [
                'dataProvider' => $medtestsProvider,
                'relation_id' => $member->member_id,
                'heading' => false,
                'expires' => true,
                'catg' => CredCategory::CATG_MEDTESTS,
            ]),
        ],
        [
            'label' => 'PD Core',
            'content' => $this->render('_summary', [
                'dataProvider' => $coreProvider,
                'relation_id' => $member->member_id,
                'heading' => false,
                'expires' => false,
                'catg'=> CredCategory::CATG_CORE,
            ]),
        ],
    ],

]);

echo Html::a(
    '<i class="glyphicon glyphicon-export"></i>&nbsp;Excel Certificate',
    ['certificate', 'member_id' => $member->member_id],
    ['class' => 'btn btn-default']
);

