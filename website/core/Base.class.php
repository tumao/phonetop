<?php

	abstract class ABaseControl extends ASingleton
	{
		protected $_session_id	= NULL;
		protected $_param_page	= 0;
		protected $_param_size	= 0;
		protected $_sql_offset	= 0;
		
		const COMMAND	= 'command';
		
		protected function __construct()
		{
			if (isset($_COOKIE['PHPSESSID']))
			{
				$this->_session_id	= $_COOKIE['PHPSESSID'];
			}
			else
			{
				$this->_session_id	= session_id();
			}
			if (isset($_REQUEST['page']))
			{
				$this->_param_page	= intval($_REQUEST['page']);
			}
			if (isset($_REQUEST['size']))
			{
				$this->_param_size	= intval($_REQUEST['size']);
			}
			if ($this->_param_page > 0)
			{
				$this->_sql_offset	= ($this->_param_page - 1) * $this->_param_size;
			}
		}
		
		abstract public function init();
		abstract public function free();
	}

?>