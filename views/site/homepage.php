<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\dropdown\DropdownX;
?>

<div id="welcome">
	<div>
		<p>Welcome to the DC50 Business Office Support portal.  By using this page,
            you are agreeing to <?= Html::a('these terms', '/site/terms'); ?>.
        </p>
        <?php if (Yii::$app->user->isGuest): ?>
            <p>Access requires a login.  If you don't have a username, you can 
                <?= Html::a('request one here', '#'); ?> or by calling Support.
            </p>
        <?php else: ?>

    	<div class="leftside sixty-pct">
		    <div class="panel panel-primary">
		        <div class="panel-heading"><h4 class="panel-title"><i class="glyphicon glyphicon-calendar"></i> Calendar</h4></div>
		        <div class="panel-body">
		       
					   <?= \yii2fullcalendar\yii2fullcalendar::widget([
					            'events' => $events,
					            'id' => 'home-calendar',
					   		    
					        ]);
					   ?> 
		        </div>
		       <div class="panel-footer"><?= Html::button('<i class="glyphicon glyphicon-plus"></i>&nbsp;Add Event', [
											'value' => Url::to(["/home-event/create"]),
											'id' => 'classCreateButton',
											'class' => 'btn btn-default btn-modal btn-embedded',
											'data-title' => 'Event',
		       ]) ?> </div>
		    </div>
		</div>
		<div class="rightside thirtyfive-pct">
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
    
    <?php endif; ?>
	</div>
	
</div>

<?= $this->render('../partials/_modal') ?>