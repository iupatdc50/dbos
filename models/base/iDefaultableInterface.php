<?php

namespace app\models\base;

/**
 * Interface for classes that have a default in context.  Concrete classes must override
 * $relationAttribute property
 * 
 * @author jmdemoor
 */
interface iDefaultableInterface {
	
	public static function createDefaultObj();
	public function getAggregate();
	public function getIsDefault();
	public function makeDefault($default);
	
}
