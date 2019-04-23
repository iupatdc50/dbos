<?php

use yii\bootstrap\Tabs;

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
            ]),
        ],
        [
            'label' => 'Non-recurring',
            'content' => $this->render('_summary', [
                'dataProvider' => $nonrecurProvider,
                'relation_id' => $member->member_id,
                'heading' => false,
                'expires' => false,
            ]),
        ],
        [
            'label' => 'Medical Tests',
            'content' => $this->render('_summary', [
                'dataProvider' => $medtestsProvider,
                'relation_id' => $member->member_id,
                'heading' => false,
                'expires' => true,
            ]),
        ],
        [
            'label' => 'PD Core',
            'content' => $this->render('_summary', [
                'dataProvider' => $coreProvider,
                'relation_id' => $member->member_id,
                'heading' => false,
                'expires' => false,
            ]),
        ],
    ],

]);

