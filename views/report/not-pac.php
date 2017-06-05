<?php

	$reportico = Yii::$app->getModule('reportico');
	$engine = $reportico->getReporticoEngine();        // Fetches reportico engine
	$engine->access_mode = "ONEREPORT";                // Allows access to single specified report
	$engine->initial_execute_mode = "PREPARE";         // Starts user in report criteria selection mode
	$engine->initial_project = "memberrpts";            // Name of report project folder
	$engine->initial_report = "notinpac";           // Name of report to run
	$engine->bootstrap_styles = "3";                   // Set to "3" for bootstrap v3, "2" for V2 or false for no bootstrap
	$engine->force_reportico_mini_maintains = true;    // Often required
	$engine->bootstrap_preloaded = true;               // true if you dont need Reportico to load its own bootstrap
	$engine->clear_reportico_session = true;           // Normally required
	$engine->execute();
?>

