<?php

	class Application extends ASingleton
	{
		private $service	= NULL;
		private $calling	= NULL;
		
		const APP_FILE_CONFIG	= 'application.ini';
		
		public static function autoload($called)
		{
			if (preg_match('/ORM$/', $called))
			{
				$name	= substr($called, 0, -3);
				if (is_file(ORM.$name.'.orm.php'))
				{
					return require(ORM.$name.'.orm.php');
				}
				return false;
			}
			if (preg_match('/Control$/', $called))
			{
				$name	= substr($called, 0, -7);
				if (is_file(APP.$name.'.class.php'))
				{
					return require(APP.$name.'.class.php');
				}
			}
			if (is_file(CORE.$called.'.class.php'))
			{
				return require(CORE.$called.'.class.php');
			}
			return false;
		}
		
		public function init()
		{
			return $this;
		}
		
		public function exec()
		{
			if (!$this->verifyServiceFromURI())
			{
				return self::pageNotFound();
			}
			call_user_func_array(array(
				$this->service,
				$this->calling
			), array());
		}
		
		/**
		 * ���캯��
		 *
		 */
		protected function __construct()
		{
			session_start();
		}
		
		public function __destruct()
		{
			if ($this->service)
			{
				$this->service->free();
			}
			unset($this->service);
		}
		
		/**
		 * ���������Ƿ�Ϸ�
		 *
		 */
		private function verifyServiceFromURI()
		{
			$position	= strpos($_SERVER['REQUEST_URI'], '?');
			$position	= $position ? $position : strlen($_SERVER['REQUEST_URI']);
			$service	= substr($_SERVER['REQUEST_URI'], 1, $position-1);
			$called	= ucfirst(strtolower($service)).'Control';
			if (!isset($_REQUEST[ABaseControl::COMMAND]))
			{
				return self::pageNotFound();
			}
			$this->calling	= strtolower($_REQUEST[ABaseControl::COMMAND]);
			$this->service	= $called::getInstance();
			if (!method_exists($this->service, $this->calling) && !method_exists($this->service, '__call'))
			{
				return self::pageNotFound();
			}
			$this->service->init();
			return true;
		}
		
		/**
		 * ҳ�治����
		 *
		 */
		public static function pageNotFound()
		{
			header('HTTP/1.1 404 Page Not Found');
			echo 'Page Not Found';
		}
		
	}
	
?>