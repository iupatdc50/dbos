<?php

use app\components\utilities\RemitGrid;
use app\modules\admin\models\FeeType;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $modelContractor app\models\contractor\Contractor */
/* @var $modelsFeeType app\models\accounting\TradeFeeType[] */
/* @var $lob_cd string */
/* @var $doc_number string */

$file_nm = 'RemitTemplate_' . $modelContractor->license_nbr . '_' . $lob_cd;
$sheetTitle = substr($modelContractor->contractor, 0, 23) . ' (' . $lob_cd . ')';

// 8 base columns
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
                    'label' => 'HR: Hours Worked',  			// column G [7]
                    'value' => null,
            ],
			[
					'label' => 'GW: Gross Wages',  				// column H [8]
					'value' => null,
			]
];

$summaryCols = [7, 8];
$col_nbr = 8;
$grandCols = [];
$groups = [
    0 => ["cols" => [7,8], "backgroundcolor" => "ffffff", "grandtotal" => false],  // white
    1 => ["cols" => [], "backgroundcolor" => "d3d3d3", "grandtotal" => true],      // light grey
    2 => ["cols" => [], "backgroundcolor" => "add8e6", "grandtotal" => true]      // light blue
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
	if ($modelFeeType->fee_type != FeeType::TYPE_HOURS) {
        $summaryCols[] = ++$col_nbr;
        $grandCols[] = $col_nbr;
        if ($modelFeeType->fee_type != FeeType::TYPE_JTP) {
            $groups[1]["cols"][] = $col_nbr;
        } else {
            $groups[2]["cols"][] = $col_nbr;
        }
    }
}

/** @noinspection PhpUnhandledExceptionInspection */
RemitGrid::widget([
        'dataProvider' => $dataProvider,
		'filename' => $file_nm,
		'properties' => [
			'sheetTitle' => $sheetTitle,
            'doc_number' => $doc_number,
		], 
		'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => ''],
		'columns' => array_merge ($base, $submittables),
        'summaryCols' => $summaryCols,
        'grandCols' => $grandCols,
        'groups' => $groups,
        'footer' => $sheetTitle . ' - ' . date('F Y'),
]);


