<?php

use yii\bootstrap\Modal;


Modal::begin([
		'header' => '<h4>Complete <span id="title-model"></span> information</h4>',
		'options' => [
			'id' => 'modalCreate',
			'tabindex' => false,  // Required for Select2 to work properly
		],
]);
echo "<div class=modal-body id='modalContent'></div>";
Modal::end();

	    
