<?php

namespace app\helpers;

use Yii;

class ClassHelper
{
	/**
	 * Helper casts an object as child
	 * 
	 * @param string $class_name  Target class
	 * @param object $source 
	 * @throws \InvalidArgumentException The source must be a parent object of the target
	 * @return object
	 */
	public static function cast($class_name, $source)
	{
		$object = new $class_name();
		if (!is_a($object, get_class($source)))
			throw new \InvalidArgumentException("`{$class_name}` is not a class of source object");
		$attrs = $source->getAttributes();
		foreach ($attrs as $name => $value) 
			$object->$name = $value;
		return $object;		
	}
}