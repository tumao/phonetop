<?php
	/**
	 * Env
	 *
	 * @package Framework\Core
	 *
	 * @author 李文强 <liwenqiang@shoujids.com>
	 * @copyright 朱雀网络
	 * @version 1.0
	 */
	namespace ZQFramework\Core
	{
		class Env extends Singleton
		{
			/**
			 * 域名
			 *
			 * @var string $DOMAIN
			 */
			private static $DOMAIN	= NULL;
			
			/**
			 * SESSION id值
			 *
			 * @var string $SESSID
			 */
			private static $SESSID	= NULL;
		
			/**
			 * SESSION id值
			 *
			 * @var string $_session_id
			 */
			private $_session_id	= NULL;
			
			/**
			 * cookie的安全KEY值
			 *
			 * @var string $_cookie_key
			 */
			private $_cookie_key	= NULL;
			
			/**
			 * uri堆栈队列
			 *
			 * 
			 */
			public $queue	= NULL;
			
			/**
			 * Env构造函数
			 *
			 * 初始化相关环境变量。
			 */
			protected function __construct()
			{
				if (!isset($_SERVER['SERVER_NAME']) || empty($_SERVER['SERVER_NAME']))
				{
					self::$DOMAIN	= $_SERVER['SERVER_ADDR'];
				}
				else
				{
					self::$DOMAIN	= $_SERVER['SERVER_NAME'];
				}
				$this->parseUriQueue();
				$this->_cookie_key	= md5(file_get_contents(__FILE__));
			}
			
			/**
			 * 初始化SESSION
			 *
			 * 初始化SESSION值，系统Application中自动调用。
			 */
			public function __init_session()
			{
				$this->_session_id	= session_id();
				if (empty($this->_session_id))
				{
					session_start();
				}
				$this->_session_id	= session_id();
				self::$SESSID	= $this->_session_id;
			}
			
			/**
			 * 是否POST请求
			 *
			 * @return bool
			 */
			public function isp()
			{
				return strtoupper($_SERVER['REQUEST_METHOD']) == 'POST';
			}
			
			/**
			 * 获取或设置session值
			 *
			 * @param string $name	session名称
			 * @param mixed $value	session值，为false时获取session值
			 * @return mixed		获取时返回session值（string），设置时返回成功或失败（bool）
			 */
			public function session($name, $value=false)
			{
				if ($value === false)
				{
					if (!isset($_SESSION[$name]))
					{
						return false;
					}
					return $_SESSION[$name];
				}
				$_SESSION[$name]	= $value;
			}
			
			public function salt($name)
			{
				$len	= strlen($name) % 3 + 3;
				$suffix	= substr(md5(md5($name.$this->_cookie_key).$this->_session_id), 0, $len);
				return	$suffix;
			}
			
			/**
			 * 获取或设置cookie值
			 *
			 * @param string $name	cookie名称
			 * @param mixed $value	cookie值，cookie值为false时获取cookie值
			 * @return mixed		获取时返回cookie值（string），设置时返回成功或失败（bool）
			 */
			public function cookie($name, $value=false, $expire=0)
			{
				$suffix	= $this->salt($name);
				$name	= $name . '_' . $suffix;
				if ($value === false)
				{
					if (!isset($_COOKIE[$name]))
					{
						return false;
					}
					return $_COOKIE[$name];
				}
				return setcookie($name, $value, $expire, '/', self::$DOMAIN);
			}
			
			/**
			 * 取URI队列中，除掉controller与action外的参数值
			 *
			 * @return string	URI中参数值
			 */
			public function param_pop()
			{
				if ($this->queue->valid())
				{
					$data	= $this->queue->current();
					$this->queue->next();
					return $data;
				}
				return false;
			}
			
			/**
			 * 设置安全cookie值
			 *
			 * @param string $name	cookie名称
			 * @return string		cookie值
			 */
			public static function getcookie($name)
			{
				return self::getInstance()->cookie($name);
			}
			
			/**
			 * 获取安全cookie值
			 *
			 * @param string $name	cookie名称
			 * @param string $value	cookie值
			 * @retrun bool			成功或失败
			 */
			public static function setcookie($name, $value, $expire=0)
			{
				return self::getInstance()->cookie($name, $value, $expire);
			}
			
			/**
			 * 通过salt值取对应的cookie值
			 *
			 * 记住密码时使用。
			 */
			public function getCookieBy($name, $salt)
			{
				$name	= $name . '_' . $salt;
				if (!isset($_COOKIE[$name]))
				{
					return false;
				}
				return $_COOKIE[$name];
			}
			
			/**
			 * 清除特定salt值的cookie
			 * 
			 * 记住密码中，使用过一次后，清除原来的salt值的cookie
			 */
			public function clearExpireCookie()
			{
				foreach ($_COOKIE as $name => $value)
				{
					$conf	= explode('_', $name);
					if (empty($conf[0])) continue;
					$salt	= $this->salt($conf[0]);
					if (isset($conf[1]) && $conf[1] != $salt)
					{
						setcookie($name, NULL, strtotime('1970-01-01 00:00:00'), '/', self::$DOMAIN);
					}
				}
				// ** ==========================================================================================
			}
			
			/**
			 * 注销时清除所有的cookie
			 *
			 */
			public function clearCookie()
			{
				foreach ($_COOKIE as $name => $value)
				{
					setcookie($name, NULL, strtotime('1970-01-01 00:00:00'), '/', self::$DOMAIN);
				}
			}
			
			/**
			 * 生成URI解析队列值
			 *
			 * 通过请求的URL生成uri队列
			 *
			 */
			private function parseUriQueue()
			{
				$this->queue	= new \SplDoublyLinkedList();
				$position_make	= strpos($_SERVER['REQUEST_URI'], '?');
				if ($position_make === false)
				{
					$position_make	= strlen($_SERVER['REQUEST_URI']);
				}
				$queue_array	= explode('/', substr($_SERVER['REQUEST_URI'], 0, $position_make));
				foreach ($queue_array as $mkey)
				{
					$mkey	= trim($mkey);
					if ($mkey != '' && preg_match('/^(\.+)$/i', $mkey)!==1)
					{
						$this->queue->push(strtolower($mkey));
					}
				}
				$this->queue->rewind();
			}
			
			
			
		}//~: __END_OF_CLASS__________
	}
?>