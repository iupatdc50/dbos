<?php

/**
 * Special version of create for employment. Launched by the Create controller action.
 */

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\member\Employment */

?>
<div class="employment-create">

    <?= $this->render('_form', [
        'model' => $model,
    	'create' => 'Employ'
    ]) ?>

</div>
