<div id="journal">
     <?php if ($model->noteCount >= 1): ?>
     	<p> <?= $model->noteCount > 1 ? $model->noteCount . ' Journal Notes' : 'One Journal Note'; ?></p>
     	<?= $this->render('../partials/_notes', ['notes' => $model->notes, 'controller' => 'project-note']); ?>
     <?php endif; ?>

	<?=  $this->render('../partials/_noteform', ['model' => $noteModel]) ?>

</div>
