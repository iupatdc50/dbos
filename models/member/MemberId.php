<?php

namespace app\models\member;

use Yii;

class MemberId implements \app\models\base\iIdInterface
{
	public function newId($seq = NULL) {
		$db = Yii::$app->db;
		$db->createCommand("CALL NewMemberID (:seq, @out)")
			->bindValue(':seq', $seq)
			->execute();
		return $db->createCommand('SELECT @out')->queryScalar();
	}
} 