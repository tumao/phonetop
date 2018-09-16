<?php

	class RedisConn
	{
		private static $mConnection	= NULL;
		
		public static function & getConnection()
		{
			if (self::$mConnection == NULL)
			{
				$conf	= require(CONF.'service'._.'redis-config-'.__ENV_GLOBAL.'.inc.php');
				self::$mConnection	= new Redis();
				self::$mConnection->connect($conf['host'], $conf['port']);
				self::$mConnection->select($conf['name']);
			}
			return self::$mConnection;
		}
		
		private function __consturct()
		{
		
		}
		
	}

?>