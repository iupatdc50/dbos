<?php

use yii\helpers\Html;
use wbraganca\dynamicform\DynamicFormWidget;
use kartik\widgets\Select2;

?>

<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_inner',
    'widgetBody' => '.container-allocations',
    'widgetItem' => '.allocation',
    'limit' => 4,
    'min' => 1,
    'insertButton' => '.add-allocation',
    'deleteButton' => '.del-allocation',
    'model' => $modelsAllocation[0],
    'formId' => 'receipt-form',
    'formFields' => [
        'fee_type',
    	'allocation_amt',
    ],
]); ?>
<table class="table table-bordered">
    <thead>
        <tr class="active">
    		<td><?= Html::activeLabel($modelsAllocation[0], 'fee_type'); ?></td>
    		<td><?= Html::activeLabel($modelsAllocation[0], 'allocation_amt'); ?></td>
            <td><button type="button" class="add-allocation btn btn-success btn-xs"><i class="glyphicon glyphicon-plus"></i></button></td>
    	</tr>
    </thead>
    <tbody class="container-allocations">
    <?php $outer = !($ixM == -1) ? "[{$ixM}]" : ''; ?>
    <?php foreach ($modelsAllocation as $ixA => $alloc): ?>
        <tr class="allocation">
            <td class="sixty-pct">
    		<?php // ** Temporary ** Assume 1791 ?>
            <?= $form->field($alloc, $outer . "[{$ixA}]fee_type")->label(false)
            		 ->widget(Select2::className(), [
			    		'data' => $modelReceipt->getFeeOptions('1791'), 
			    		'options' => ['placeholder' => 'Select...'],
			    ])  
			    		
            ?>
            </td><td>
                <?= $form->field($alloc, $outer . "[{$ixA}]allocation_amt")->label(false)->textInput(['maxlength' => true]) ?>
            </td>
            <td>
            	<button type="button" class="del-allocation btn btn-danger btn-xs"><i class="glyphicon glyphicon-minus"></i></button>
            	<?php
                    // necessary for update action.
                    if (! $alloc->isNewRecord) {
                        echo Html::activeHiddenInput($alloc, $outer . "[{$ixA}]id");
                    }
                ?>
            </td>
        </tr>
     <?php endforeach; ?>
    </tbody>
</table>
<?php DynamicFormWidget::end(); ?>