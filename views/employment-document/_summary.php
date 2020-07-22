<?php

use yii\data\ActiveDataProvider;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider ActiveDataProvider */
/* @var $member_id string */
/* @var $effective_dt string */

?>

<div id="document-panel" class="leftside forty-pct">

<?php

$controller = 'employment-document';

// 'id' of Pjax::begin and embedded GridView::widget must match or pagination does not work
Pjax::begin(['id' => 'document-grid', 'enablePushState' => false]);

/** @noinspection PhpUnhandledExceptionInspection */
echo GridView::widget([
    'id' => 'document-grid',
    'dataProvider' => $dataProvider,
    'pjax' => false,
    'panel'=>[
        'type'=>GridView::TYPE_DEFAULT,
        'heading'=>'<i class="glyphicon glyphicon-folder-close"></i>&nbsp;Documents',
        'class' => 'text-primary',
        'before' => false,
        'after' => false,
        // 'footer' => false,
    ],
    'columns' => [
        'extendedType',
        [
            'attribute' => 'showPdf',
            'label' => 'Doc',
            'format' => 'raw',
            'value' => function($model) {
                return (isset($model->doc_id)) ?
                    Html::a(Html::beginTag('span', ['class' => 'glyphicon glyphicon-paperclip', 'title' => 'Show document']),
                        $model->imageUrl, ['target' => '_blank', 'data-pjax'=>"0"]) : '';
            },
        ],
        [
            'class' => 	'kartik\grid\ActionColumn',
            'visible' => Yii::$app->user->can('uploadDocs'),
            'controller' => $controller,
            'template' => '{delete}',
            'header' => Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add',
                [
                    'value' => Url::to([
                        "/{$controller}/create",
                        'member_id'  => $member_id,
                        'effective_dt' => $effective_dt,
                    ]),
                    'id' => 'documentCreateButton',
                    'class' => 'btn btn-default btn-modal btn-embedded',
                    'data-title' => 'Document',
                ]),
        ],
    ],
]);
?>

    </div>
<?php

Pjax::end();
