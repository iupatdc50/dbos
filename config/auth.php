<?php
return [ 
				'class'	=> 'yii\rbac\DbManager',
				'defaultRoles' => ['guest'],
				'assignmentTable' => 'AuthAssignments',
				'itemChildTable' => 'AuthItemChilds',
				'itemTable' => 'AuthItems',
				'ruleTable' => 'AuthRules',
];