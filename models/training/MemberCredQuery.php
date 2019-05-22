<?php

namespace app\models\training;

use yii\db\ActiveQuery;

/** @noinspection MissingActiveRecordInActiveQueryInspection */

class MemberCredQuery extends ActiveQuery
{

    public $credential_id;
    public $tableName;

    public function prepare($builder)
    {
        if ($this->type !== null) {
            $this->andWhere(["$this->tableName.credential_id" => $this->credential_id]);
        }
        return parent::prepare($builder);
    }

}