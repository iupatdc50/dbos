<?php

namespace app\models\base;

use Yii;
use app\components\utilities\OpDate;

/**
 * BaseEndable handles end dates after inserts and deletes
 * 
 * Classes that extend BaseEndable should identify qualifying column by overriding static function
 * qualifier()
 */
class BaseEndable extends \yii\db\ActiveRecord
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
	 * Removes the end date on the most current row.  Can be used to open an entry when the opn entry 
	 * is deleted
	 * 
	 * @param string $id  Identifies the searchable column on the table
	 */
	public static function openLatest($id)
	{
		/* @var $query yii\db\ActiveQuery */
		$query = call_user_func([self::className(), 'find']);
		if (null !== static::qualifier())
			$query->where([static::qualifier() => $id]);
		$query->orderBy('effective_dt DESC');
		$model = $query->orderBy('effective_dt DESC')->one();
		$model->end_dt = null;
		$model->save();
	}
	
	public function afterSave($insert, $changedAttributes)
	{
		parent::afterSave($insert, $changedAttributes);
		if ($insert)
			$this->closePrevious();
	}
	
	protected function closePrevious()
	{
		/* @var $end_dt OpDate */
		$end_dt = (new OpDate())->setFromMySql($this->effective_dt)->sub(new \DateInterval('P1D'));
		$qualifier = $this->qualifier();
		$condition = "{$qualifier} = '{$this->$qualifier}' AND end_dt IS NULL AND effective_dt <> '{$this->effective_dt}'";
		call_user_func([$this->className(), 'updateAll'], ['end_dt' => $end_dt->getMySqlDate()], $condition);
	}
	
}