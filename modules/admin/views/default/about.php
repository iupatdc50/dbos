<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
$this->title = 'About DBOS';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><span class="label label-warning">Version </span><span class="label label-primary">0.9.4.001 (Beta 1)</span> Committed to GitHub repository</p>
    <p><span class="label label-warning">Release Date </span><span class="label label-primary">June 17, 2015</span></p>
    
	    <div class="panel panel-warning">
	        <div class="panel-heading"><h4 class="panel-title"><i class="glyphicon glyphicon-tags"></i>&nbsp;Current Issues</h4></div>
	        <div class="panel-body">
		        <h5 class="text-warning">Security</h5>
		        <ul>
		        	<li>Security RBAC not active.  Disabled blocked features.</li>
		        	<li>User identity resets on occasion.</li>
		        </ul>
		        <h5 class="text-warning">Navigation</h5>
		        <ul>
		        	<li>Database caching not yet implemented.</li>
	  	        	<li>Some breadcrumbs in sub-window updates are incorrect.</li>
		        	<li>Agreements accordion closes after a panel content update.</li>
		        </ul>
		        <h5 class="text-warning">Special Projects</h5>
		        <ul>
	  	        	<li>Deleting last registration in a project causes crash.  JTP project should not have 0 registrations</li>
		        </ul>
		        
			</div>
	    </div>
</div>
