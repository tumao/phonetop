<?php

	namespace ZQFramework\Core
	{
		if (!defined('_FRAMEWORK_CORE_SINGLETON'))
		{
			define('_FRAMEWORK_CORE_SINGLETON', true);
			
			abstract class Singleton
			{
				private static $mInstancePool	= array();
				
				public static function & getInstance()
				{
					$called	= get_called_class();
					$mkey	= md5($called);
					if (!isset(self::$mInstancePool[$mkey]) || self::$mInstancePool[$mkey] == NULL)
					{
						self::$mInstancePool[$mkey]	= new $called;
					}
					return self::$mInstancePool[$mkey];
				}
			}
		}
	}
?>