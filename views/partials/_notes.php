<?php 

use yii\helpers\Html;

/* @var $notes \yii\db\ActiveQuery */
/* @var $controller string */
?>

<?php foreach ($notes as $entry): ?>

	<div class="note">
		<div class="note-time">
			On <?=  date('F j, Y \a\t h:i a', $entry->created_at) . ', ' . Html::encode($entry->author->username) . ' posted:'; ?>
			<div class="pull-right">
			<?= Html::a(Html::beginTag('span', ['class' => 'glyphicon glyphicon-trash']), 
					["/{$controller}/delete", 'id' => $entry->id],
					['data' => [
		                'confirm' => 'Are you sure you want to delete this note?',
		                'method' => 'post',
		            ]]
			); ?>
			</div>
		</div>
		<div class="well well-sm">
			<?= nl2br(Html::encode($entry->note)); ?>
		</div>
	</div>

<?php endforeach; ?>