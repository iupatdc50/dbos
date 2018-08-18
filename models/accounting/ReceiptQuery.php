<?php

namespace app\models\accounting;

use yii\db\ActiveQuery;

class ReceiptQuery extends ActiveQuery
{

    public $type;
    public $tableName;

    public function prepare($builder)
    {
        if ($this->type !== null) {
            $this->andWhere(["$this->tableName.payor_type" => $this->type]);
        }
        return parent::prepare($builder);
    }

}