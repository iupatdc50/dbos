<?php

use app\components\utilities\ExcelGrid;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$file_nm = 'UniversalFile_' . date('ymd');
$sheetTitle = 'District Council 50';

/** @noinspection PhpUnhandledExceptionInspection */
ExcelGrid::widget([
    'dataProvider' => $dataProvider,
    'filename' => $file_nm,
    'properties' => [
        'sheetTitle' => $sheetTitle,
        'doc_number' => '',
    ],
    'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => ''],
    'columns' => [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV',
        'AW', 'AX'
    ],
    'summaryCols' => null,
]);
