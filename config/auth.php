<?php
return [ 
				'class'	=> 'yii\rbac\DbManager',
				'defaultRoles' => ['guest'],
				'assignmentTable' => 'AuthAssignments',
				'itemChildTable' => 'AuthItemChilds',
				'itemTable' => 'AuthItems',
				'ruleTable' => 'AuthRules',
                'cache' => 'cache',     // tas 9/15/25 -- reduce number of authentication queries
];