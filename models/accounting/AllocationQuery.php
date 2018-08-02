<?php

namespace app\models\accounting;

use yii\db\ActiveQuery;

class AllocationQuery extends ActiveQuery
{

    public $type;
    public $tableName;

    public function prepare($builder)
    {
        if ($this->type !== null) {
            $this->andWhere(["$this->tableName.fee_type" => $this->type]);
        }
        return parent::prepare($builder);
    }

}