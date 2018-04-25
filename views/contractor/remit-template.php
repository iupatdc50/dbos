<?php

use app\components\utilities\ExcelGrid;
use app\modules\admin\models\FeeType;

/* @var $this \yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $modelContractor app\models\contractor\Contractor */
/* @var $modelsFeeType app\models\accounting\TradeFeeType[] */
/* @var $lob_cd string */

$file_nm = 'RemitTemplate_' . $modelContractor->license_nbr . '_' . $lob_cd;
$sheetTitle = substr($modelContractor->contractor, 0, 23) . ' (' . $lob_cd . ')';

// 9 base columns
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
                    'label' => 'HR: Hours Worked',  			// column H [8]
                    'value' => null,
            ],
			[
					'label' => 'GW: Gross Wages',  				// column I [9]
					'value' => null,
			],
];

$summaryCols = [8, 9];
$col_nbr = 9;

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
	if ($modelFeeType->fee_type != FeeType::TYPE_HOURS)
	    $summaryCols[] = ++$col_nbr;
}

/** @noinspection PhpUnhandledExceptionInspection */
ExcelGrid::widget([
		'dataProvider' => $dataProvider,
		'filename' => $file_nm,
		'properties' => [
				'sheetTitle' => $sheetTitle,
		], 
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => ''],
		'columns' => array_merge ($base, $submittables),
        'summaryCols' => $summaryCols,
]);


