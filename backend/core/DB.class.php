<?php


	class DB extends ZQFramework\Core\DBProvider
	{
	
	}
	
	class ORM extends ZQFramework\Core\DBORM
	{
		protected $rdconn	= NULL;
		
		public function __construct()
		{
			parent::__construct();
			$this->rdconn	= RedisConn::getConnection();
		}
		
		
	}


?>