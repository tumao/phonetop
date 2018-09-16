<?php

	abstract class ASingleton
	{
		protected static $arrInstance	= array();
		
		public static function &getInstance()
		{
			$called	= get_called_class();
			if (!isset(self::$arrInstance[$called]))
			{
				self::$arrInstance[$called]	= new $called;
			}
			return self::$arrInstance[$called];
		}
	}

?>