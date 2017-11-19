<?php

use app\components\utilities\ExcelGrid;
use yii\bootstrap\Alert;
use app\modules\admin\models\FeeType;

$file_nm = 'RemitTemplate_' . $modelContractor->license_nbr . '_' . $lob_cd;
$sheetTitle = substr($modelContractor->contractor, 0, 23) . ' (' . $lob_cd . ')';

// 5 base columns
$base = [
		
			[
					'attribute' => 'classification', 			// column A
					'label' => 'Class',  
			],
			'last_nm',											// column B
			'first_nm',											// column C
			[
					'attribute' => 'middle_inits',  			// column D
					'label' => 'MI',
			],
			'report_id',										// column E
			[
					'attribute' => 'member_status', 			// column F
					'label' => 'I',					
			],
			[
					'attribute' => 'standing.billingDescrip', 	//column G
					'label' => 'Remarks',
			],
			[
					'label' => 'GW: Gross Wages',  				// column H
					'value' => null,
			],
			[
					'label' => 'HR: Hours Worked',  			// column I
					'value' => null,
			],
];

$submittables = [];
foreach ($modelsFeeType as $modelFeeType) {
	if($modelFeeType->contribution == 'T')
		$submittables[] = [
				'attribute' => $modelFeeType->fee_type,
				'label' => $modelFeeType->colHead,
		];
	elseif($modelFeeType->fee_type != FeeType::TYPE_HOURS)
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


