<?php

namespace app\models\base;

use DateInterval;
use Exception;
use Yii;
use app\components\utilities\OpDate;
use yii\db\ActiveRecord;

/** @noinspection UndetectableTableInspection */

/**
 * BaseEndable handles end dates after inserts and deletes
 *
 * @property string $effective_dt
 * @property string $end_dt
 *
 * Classes that extend BaseEndable should identify qualifying column by overriding static function
 * qualifier()
 */
class BaseEndable extends ActiveRecord
{

	/**
	 * Identifies the qualifying column.  If not overridden, entire table is searched.
	 * 
	 * @return NULL
	 */
	public static function qualifier() 
	{
		return null;
	}
	
	/**
	 * Removes the end date on the most current row.  Can be used to open an entry when the open entry
	 * is deleted
	 * 
	 * @param string|array $id  Identifies the searchable column(s) on the table. Can be an array of key => val to handle
     *                          multiple qualifying columns
	 */
	public static function openLatest($id)
	{
		/* @var $query yii\db\ActiveQuery */
		$query = call_user_func([self::className(), 'find']);
		if (is_array($id))
		    $query->where($id);
		elseif (null !== static::qualifier())
			$query->where([static::qualifier() => $id]);
		if (($model = $query->orderBy('effective_dt DESC')->one()) != null) {
            /** @var BaseEndable $model */
            $model->end_dt = null;
			$model->save();
		}
	}

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @throws Exception
     */
	public function afterSave($insert, $changedAttributes)
	{
		parent::afterSave($insert, $changedAttributes);
		if ($insert)
			$this->closePrevious();
		elseif (isset($changedAttributes['effective_dt']))
			$this->restripe();
	}

    /**
     * @throws Exception
     */
	protected function closePrevious()
	{
        $end_dt = (new OpDate())->setFromMySql($this->effective_dt)->sub(new DateInterval('P1D'));
        $condition = $this->buildQualClause();
		$condition .= " AND end_dt IS NULL AND effective_dt < '$this->effective_dt'";
		call_user_func([$this->className(), 'updateAll'], ['end_dt' => $end_dt->getMySqlDate()], $condition);
	}

    /**
     * @throws Exception
     */
	protected function restripe()
	{
		$condition = $this->buildQualClause();
		/* @var $query yii\db\ActiveQuery */
		$query = call_user_func([self::className(), 'find']);
		/** @var OpDate $end_dt */
		$end_dt = null;
		$rows = $query->where($condition)->orderBy('effective_dt DESC')->all();
        /** @var BaseEndable $row */
		foreach ($rows as $row) {
			try {
                $row->end_dt = (is_null($end_dt)) ? null : $end_dt->getMySqlDate();
			    $row->save();
			} catch (Exception $e) {
				Yii::error('Problem with row save. Messages: ' . print_r($e->getMessage(), true));
			}
			$end_dt = (new OpDate())->setFromMySql($row->effective_dt)->sub(new DateInterval('P1D'));
		}
		
	}

	private function buildQualClause()
    {
        $qualifier = $this->qualifier();
        if (is_array($qualifier)) {
            $exp = [];
            foreach ($qualifier as $col)
                $exp[] = "{$col} = '{$this->$col}'";
            return implode(' AND ', $exp);
        } else
            return "{$qualifier} = '{$this->$qualifier}'";
    }
	
}