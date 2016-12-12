<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\dropdown\DropdownX;
?>

<div id="welcome">
	<div>
	    <h3><i class="glyphicon glyphicon-play-circle"></i> Start Here</h3>
		<p>Welcome to the DC50 Business Office Support portal.  By using this page,
            you are agreeing to <?= Html::a('these terms', '/site/terms'); ?>.
        </p>
        <?php if (Yii::$app->user->isGuest): ?>
            <p>Access requires a login.  If you don't have a username, you can 
                <?= Html::a('request one here', '#'); ?> or by calling Support.
            </p>
        <?php else: ?>
	    <div class="panel panel-primary sixty-pct">
	        <div class="panel-heading"><h4 class="panel-title"><i class="glyphicon glyphicon-th-list"></i> System Components</h4></div>
	        <div class="panel-body">
		<table class="table-striped">
			<tr class="three-em">
				<td class="twelve-em">
					<?= Html::button('Membership', [
							'value' => '/member', 
							'class' => 'btn btn-info ten-em btn-aslink',
							'disabled' => !(Yii::$app->user->can('browseMember')),
					]); ?>
				</td>
				<td>For viewing and editing member records by trade. Depending on
					permissions, individual payments may be posted here also.</td>
			</tr>
			<tr class="three-em">
				<td class="twelve-em">
					<?= Html::button('Contractors', [
							'value' => '/contractor',
							'class' => 'btn btn-info ten-em btn-aslink',
							'disabled' => !(Yii::$app->user->can('browseContractor')),
					]); ?>
				</td>
				<td>For viewing and editing contractor records. Depending on
					permissions, contractor payments may be posted here.</td>
			</tr>
			<tr class="three-em">
				<td class="twelve-em">
					<?= Html::beginTag('div', ['class'=>'dropdown']); ?>
					<?= Html::button('Projects <span class="caret"></span>', [
						'type' => 'button',
						'class' => 'btn btn-info ten-em',
						'data-toggle' => 'dropdown',
						'disabled' => !(Yii::$app->user->can('browseProject')),
					]); ?>
					<?= DropdownX::widget([
					    'items' => [
					        ['label' => 'LMA Projects', 'url' => '/project-lma'],
					        ['label' => 'JTP Projects', 'url' => '/project-jtp'],
					    ],
					]); ?>
					<?= Html::endTag('div'); ?>
				</td>
				<td>For managing special ancillary agreement projects. </td>
			</tr>
			<tr class="three-em">
				<td class="twelve-em">
					<?= Html::button('Accounting', [
							'value' => '/accounting', 
							'class' => 'btn btn-info ten-em btn-aslink',
							'disabled' => !(Yii::$app->user->can('browseReceipt')),
					]); ?>
				</td>
				<td>For tracking payment activity and general payment posting.
					Receipt book balancing and adjustment transactions can also be
					accessed from here.</td>
			</tr>
			<tr class="three-em">
				<td class="twelve-em">
					<?= Html::button('Training', [
							'value' => 'unavailable', 
							'class' => 'btn btn-info ten-em btn-aslink',
							'disabled' => !(Yii::$app->user->can('manageTraining')),
					]); ?>
				</td>
				<td>For tracking compliance, training schedules and apprenticeship.</td>
			</tr>
		</table>
			</div></div>
    </div>
	<div class="sixty-pct">
	    <div class="panel panel-primary">
	        <div class="panel-heading"><h4 class="panel-title"><i class="glyphicon glyphicon-bullhorn"></i> News and Announcements</h4></div>
	        <div class="panel-body">
				<div id="journal">
			     <?php  if (count($announcements) >= 1): ?>
			     	<?= $this->render('../partials/_notes', ['notes' => $announcements, 'controller' => 'site']); ?>
			     <?php  endif; ?>
			
				<?=  $this->render('../partials/_noteform', ['model' => $announcementModel]) ?>
				</div>				
			</div>
		</div>
    <?php endif; ?>
	</div>
	<div class="sixty-pct">
		<div class="panel panel-info">
	        <div class="panel-heading"><h4 class="panel-title"><i class="glyphicon glyphicon-link"></i> Useful Links</h4></div>
	        <div class="panel-body">
				<ul class="noBullets">
					<li><a href="http://www.dc50.org" target="_blank">District Council 50 Site</a></li>
					<li><a href="http://www.capitol.hawaii.gov/session2010/" target="_blank">State
							Legislature Bill Status</a></li>
					<li><a href="http://pvl.ehawaii.gov/pvlsearch/app" target="_blank">Hawaii PVL Search</a></li>
					<li><a href="http://www.iupat.org/" target="_blank">IUPAT</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>

