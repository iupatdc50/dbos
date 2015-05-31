<?php

namespace app\helpers;

use Yii;

class MenuHelper
{
	/**
	 * Explodes current route and determines its root
	 * 
	 * @param string $route
	 * @param string $id
	 * @return boolean
	 */
	public static function isItemActive($route, $id)
	{
		$menu = explode("/", $route);
		return substr($menu[0], 0, strlen($id)) == $id ? true : false;
	}
}