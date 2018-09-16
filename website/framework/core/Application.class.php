<?php
	/**
	 * Application
	 *
	 * @package Framework\Core
	 *
	 * @author 李文强 <liwenqiang@shoujids.com>
	 * @copyright 朱雀网络
	 * @version 1.0
	 */
	namespace ZQFramework\Core
	{
		class Application extends Singleton
		{

			/**
			 * Controller实例对象
			 */
			private $controller	= NULL;

			/**
			 * Controller类名
			 */
			private $called		= NULL;
			/**
			 * Controller类调用方法
			 */
			private $calling	= NULL;

			/**
			 * 保存Controller与action数组状态
			 *
			 * Controller与action状态
			 * 用于自动对应模板文件
			 * 及权限控制等等相关的操作
			 */
			private $query_control_action	= array();

			/**
			 * Controller类名后缀
			 */
			const APP_CLASS_CONTROL		= 'Controller';
			/**
			 * Action方法名后缀
			 */
			const APP_METHOD_ACTION		= 'Action';

			/**
			 * Application构造函数
			 *
			 * 调用Env::__init_session()初始化session环境，
			 * 自动解析module及action相关业务操作。
			 */
			protected function __construct()
			{
				if (defined('CACHE') && !is_dir(CACHE))
				{
					mkdir(CACHE);
				}
				Env::getInstance()->__init_session();
				$this->checkconf();
			}

			public function __destruct()
			{

			}

			/**
			 * 执行业务处理相关
			 *
			 *
			 */
			public function startup()
			{
				$this->initController();
				if (empty($this->calling))
				{
					$this->initAction();
				}
				array_push($this->query_control_action, $this->calling);
				$this->controller->__exec_prepare_query($this->query_control_action);
				Log::getInstance()->fire('Action: '.$this->calling, FIREPHP_LEVEL_INFO);
				call_user_func_array(array(
					$this->controller,
					$this->calling.self::APP_METHOD_ACTION
				), array());
				$this->controller->__exec_finish_dispatch();
				StreamProvider::getInstance()->output();
			}

			/**
			 * 初始化Controller
			 *
			 *
			 */
			private function initController()
			{
				$tagname	= NULL;
				$basedir	= APP;
				$this->do_query_uri($tagname, $basedir);
				if (empty($this->called))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::PAGE_NOT_FOUND);
				}
				$controller_called	= $this->called;
				$this->controller	= $controller_called::getInstance();
				$this->controller->callingInit();
			}

			/**
			 * 初始化调用方法
			 *
			 */
			private function initAction()
			{
				Env::getInstance()->queue->next();
				$calling	= Env::getInstance()->queue->current();
				$calling	= preg_replace('/(\.php|\.jsp|\.do|\.asp|\.aspx)$/', '', $calling);
				if (method_exists($this->controller, $calling.self::APP_METHOD_ACTION))
				{
					$this->calling	= $calling;
					Env::getInstance()->queue->next();
				}
				else
				{
					$this->calling	= DEFAULT_ACTION;
				}
			}

			/**
			 * 检查环境设置
			 *
			 * 通过预定义量验证app相关的环境
			 * app实现文件路径，uri解析规则。
			 */
			private function checkconf()
			{
				// app文件夹根目录
				if (!defined('APP'))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::APPLICTION_DIR_NOT_DEFINED);
				}
				// app文件夹是否存在
				if (!is_dir(APP))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::APPLICTION_DIR_NOT_EXISTS);
				}
			}

			/**
			 * 检验URI是否有对应的controller
			 *
			 * 通过Env中的URI队列，验证当前的controller
			 */
			private function do_query_uri(&$tag, &$dir, $next=true)
			{
				if (Env::getInstance()->queue->isEmpty())
				{
					$tag			= DEFAULT_CONTROL;
					$this->calling	= DEFAULT_ACTION;
				}
				else if (Env::getInstance()->queue->valid())
				{
					$tag	= Env::getInstance()->queue->current();
				}
				else
				{
					if (!$next)
					{
						Env::getInstance()->queue->rewind();
					}
					return true;
				}
				array_push($this->query_control_action, $tag);
				Log::getInstance()->fire($tag.': '.$dir, FIREPHP_LEVEL_INFO);
				$checked	= false;
				$file		= NULL;
				$called		= NULL;
				if ($next && is_dir($dir.$tag))
				{
					$sum	= Env::getInstance()->queue->count();
					$key	= Env::getInstance()->queue->key();
					if ($key + 1 < $sum)
					{
						Env::getInstance()->queue->next();
						$dir	= $dir.$tag._;
						$tag	= NULL;
						return $this->do_query_uri($tag, $dir, $next);
					}
					elseif (is_file($dir.$tag._.DEFAULT_CONTROL.'.class.php'))
					{
						$file	= $dir.$tag._.DEFAULT_CONTROL.'.class.php';
						$called	= ucfirst(DEFAULT_CONTROL).self::APP_CLASS_CONTROL;
					}
				}
				if (empty($called))
				{
					if (!is_file($dir.$tag.'.class.php'))
					{
						array_pop($this->query_control_action);
						$tag			= DEFAULT_CONTROL;
						$this->calling	= DEFAULT_ACTION;
						array_push($this->query_control_action, $tag);
					}
					if (is_file($dir.$tag.'.class.php'))
					{
						$file	= $dir.$tag.'.class.php';
						$called	= ucfirst($tag).self::APP_CLASS_CONTROL;
					}
				}
				if (!empty($called))
				{
					require($file);
					if (class_exists($called) && $this->check_controller_class($called))
					{
						Log::getInstance()->fire("Controller: {$tag}", FIREPHP_LEVEL_INFO);
						$checked		= true;
						$this->called	= $called;
					}
				}
				if (!$checked)
				{
					Env::getInstance()->queue->prev();
					$tag	= NULL;
					$dir	= substr($dir, 0, strrpos($dir, _, -2)+1);
					$this->calling	= NULL;
					array_pop($this->query_control_action);
					return $this->do_query_uri($tag, $dir, false);
				}
				return false;
			}

			/**
			 * 验证查询到的controller类是否合法
			 *
			 * @param string $called	Controller类名
			 * @return bool				true时，为合法的类
			 */
			private function check_controller_class($called)
			{
				$reflection	= new \ReflectionClass($called);
				if (!$reflection->hasMethod(DEFAULT_ACTION.self::APP_METHOD_ACTION))
				{
					Log::getInstance()->fire($called.'::'.DEFAULT_ACTION.self::APP_METHOD_ACTION.' not define!', FIREPHP_LEVEL_ERROR);
					return false;
				}
				$found	= false;
				while ($parent = $reflection->getParentClass())
				{
					$name	= $reflection->getName();
					if ($name == 'ZQFramework\Core\ControllerProvider')
					{
						$found	= true;
						break;
					}
					$reflection	= $parent;
				}
				if (!$found)
				{
					Log::getInstance()->fire($called.' not extend ZQFramework\Core\ControllerProvider!', FIREPHP_LEVEL_ERROR);
				}
				return $found;
			}


		}//~: __END_OF_CLASS__________
	}
?>