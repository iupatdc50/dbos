<?php

use yii\bootstrap\ActiveForm;
use kartik\widgets\FileInput;

/* @todo Capture webcam image as an alternative to upload. Script launches webcam. (Chrome does not seem to pick up css for class params) */

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="photo-form">

    <?php $form = ActiveForm::begin([
    		'id' => 'memberPhoto-form', 
    		'enableClientValidation' => true,
    		'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <!--
    <div class="booth">
        <video id="video" class="video" autoplay></video>
    </div>
    -->

    <?= $form->field($model, 'photo_file')->widget(FileInput::className(), [
    		'options' => ['accept' => 'image/*']]);    ?>
    
    <?php ActiveForm::end(); ?>

</div>

<?php
/*
$script = <<< JS

(function () {
    navigator.mediaDevices.getUserMedia({ video: true, audio: false})
      .then(function(stream) {
          video.srcObject = stream
      })  
      .catch(function(err) {
          log(err.name + ": " + err.message)
      }); 
    
}) ();




JS;
 $this->registerJs($script);
*/

