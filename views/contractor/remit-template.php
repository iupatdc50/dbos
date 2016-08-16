<?php

use app\components\utilities\ExcelGrid;

ExcelGrid::widget([
		'dataProvider' => $dataProvider,
		'filename' => $file_nm,
		'properties' => [
				'title' => 'Remittance Template',
		], 
		'columns' => [
				'member.last_nm',
				'member.first_nm',
				'member.report_id',
		],
]);

?>