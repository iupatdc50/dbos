<?php

namespace app\models\project;

use Yii;

class ProjectId implements \app\models\base\iIdInterface
{
	public function newId($seq = NULL) {
		$db = yii::$app->db;
		$db->createCommand("CALL NewProjectID (:seq, @out)")
		->bindValue(':seq', $seq)
		->execute();
		return $db->createCommand('SELECT @out')->queryScalar();
	}
	
}