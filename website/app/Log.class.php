<?php

	class LogControl extends ABaseControl
	{
		private $orm	= NULL;
		
		/**
		 * 同一操作两次记录的时间间隔
		 */
		const TIME_INTERVAL	= 30;
		
		public function init()
		{
			unset($_REQUEST['log']);
		}
		
		public function free()
		{
			$this->__set_output();
		}
		
		public function __call($method, $param)
		{
			$this->orm	= LogDataORM::getInstance();
			$info	= array(
				'command'	=> trim($_REQUEST['command']),
				'uuid'		=> $this->_session_id,
				'data'		=> json_encode($_REQUEST),
				'ip'		=> $_SERVER['REMOTE_ADDR'],
				'httpref'	=> $_SERVER['REQUEST_URI']
			);
			if (!$this->checkInterval($info['command'], $info['ip']))
			{
				return true;
			}
			return $this->orm->insert($info);
		}
		
		private function checkInterval($command, $ip)
		{
			$uukey	= '.cache_log_'.md5($this->_session_id.$command.$ip);
			if ($this->orm->getRdconn()->exists($uukey))
			{
				return false;
			}
			$this->orm->getRdconn()->setex($uukey, self::TIME_INTERVAL, time());
			return true;
		}
		
		private function __set_output()
		{
			ob_clean();
			echo '0';
			ob_end_flush();
		}
		
		
	}
?>