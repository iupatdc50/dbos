<?php

use app\components\utilities\ExcelGrid;
use yii\bootstrap\Alert;

$file_nm = 'RemitTemplate_' . $modelContractor->license_nbr . '_' . $lob_cd;
$sheetTitle = substr($modelContractor->contractor, 0, 23) . ' (' . $lob_cd . ')';

// 5 base columns
$base = [
		'member.last_nm',
		'member.first_nm',
		'member.report_id',
		[
				'attribute' => 'standing.billingDescrip',
				'label' => 'Remarks',
		],
		[
				'label' => 'Gross Wages',
				'value' => null,
		],
];

$submittables = [];
foreach ($modelsFeeType as $modelFeeType) {
	$submittables[] = [
			'label' => $modelFeeType->colHead,
			'value' => null,
	];
}

ExcelGrid::widget([
		'dataProvider' => $dataProvider,
		'filename' => $file_nm,
		'properties' => [
				'sheetTitle' => $sheetTitle,
		], 
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => ''],
		'columns' => array_merge ($base, $submittables),
]);


