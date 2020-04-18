<?php

namespace app\models\base;

/**
 * Interface for classes that have a default in context.  Concrete classes must override
 * $relationAttribute property
 * 
 * @author jmdemoor
 */
interface iDemographicInterface {
	
	public function getAddressDefault();
	public function getPhoneDefault();

}
