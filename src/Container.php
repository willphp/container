<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: 无念  <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
namespace willphp\container;
use willphp\container\build\ContainerBuilder;
class Container {
	protected static $link = null;
	public static function single()	{
		if (!self::$link) {
			self::$link = new ContainerBuilder();
		}		
		return self::$link;
	}	
	public function __call($method, $params) {
		return call_user_func_array([self::single(), $method], $params);
	}	
	public static function __callStatic($name, $arguments) {
		return call_user_func_array([self::single(), $name], $arguments);
	}
}