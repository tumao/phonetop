<?php
	/**
	 * Log
	 *
	 * @package Framework\Core
	 *
	 * @author 李文强 <liwenqiang@shoujids.com>
	 * @copyright 朱雀网络
	 * @version 1.0
	 */
	namespace ZQFramework\Core
	{
		class Log extends Singleton
		{

			private $firephp	= NULL;

			/**
			 * Log构造函数
			 *
			 * 日志记录相关初始化
			 */
			protected function __construct()
			{
				if (defined('FIREPHP_DIR') && defined('FIREPHP_LOG_FLAG') && FIREPHP_LOG_FLAG)
				{
					require(FIREPHP_DIR.'FirePHP.class.php');
					require(FIREPHP_DIR.'fb.php');
					$this->firephp	= \FirePHP::getInstance(true);
				}
			}

			/**
			 * 记录日志
			 *
			 * 写入日志记录，自动补入时间
			 *
			 * @param string $message	日志信息
			 * @param int $level		日志等级
			 */
			public function log($message, $level=0)
			{
				$logstring	= "[";
				$logstring .= date('Y-m-d H:i:s');
				$logstring .= "]\t";
				$logstring .= $message;

			}

			/**
			 * firephp调试输出记录
			 *
			 * @param string $message	日志记录信息
			 * @param int    $level		日志记录等级
			 */
			public function fire($message, $level=FIREPHP_LEVEL_LOG)
			{
				if (empty($this->firephp))
				{
					return true;
				}
				switch ($level)
				{
					case FIREPHP_LEVEL_ERROR:
						$logmeth	= 'error';
					break;
					case FIREPHP_LEVEL_WARN:
						$logmeth	= 'warn';
					break;
					case FIREPHP_LEVEL_INFO:
						$logmeth	= 'info';
					break;
					case FIREPHP_LEVEL_LOG:
					default:
						$logmeth	= 'log';
				}
				$this->firephp->$logmeth($message);
			}

			public function __destruct()
			{

			}

		}//~: __END_OF_CLASS__________
	}

?>