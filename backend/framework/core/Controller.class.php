<?php
	/**
	 * Controller
	 *
	 * @package Framework\Core
	 *
	 * @author 李文强 <liwenqiang@shoujids.com>
	 * @copyright 朱雀网络
	 * @version 1.0
	 */

	namespace ZQFramework\Core
	{

		abstract class ControllerProvider extends Singleton
		{
			protected $io	= NULL;
			protected $env	= NULL;
			protected $log	= NULL;

			/**
			 * 全局钩子变量数组
			 *
			 * 用于扩展监听类型事件
			 */
			protected $hookit	= array();

			/**
			 * 实现IView接口的类名
			 *
			 * 用于展示页面，默认使用Smarty扩展模板
			 * 在StreamProvider中调用构造函数生成实例对象
			 */
			protected $_template_class_name	= NULL;

			/**
			 * 当前使用的模板样式
			 *
			 * $_template_class_name 类名初始化函数中使用的参数
			 */
			protected $_template_skin_used	= NULL;

			/**
			 * 模板根节点与模板文件名
			 *
			 * 用于重定向当前使用的模板文件名
			 * 若 $_template_name 为空
			 * 则使用 $_template_query 数组自动生成
			 */
			private $_template_base		= NULL;
			private $_template_name		= NULL;
			private $_template_query	= NULL;

			private $_is_init	= false;


			const DEFAULT_TEMPLATE_CLASS_NAME	= 'ZQFramework\Core\SmartyTemplate';
			const DEFAULT_TEMPLATE_SUFFIX		= '.tpl';

			/**
			 * 在构造函数中调用
			 *
			 */
			abstract protected function init();

			/**
			 * 在释构函数中调用
			 *
			 */
			abstract protected function free();

			/**
			 * ControllerProvider构造函数
			 *
			 */
			protected function __construct()
			{
				$this->io	= StreamProvider::getInstance();
				$this->env	= Env::getInstance();
				$this->log	= Log::getInstance();
				if (empty($this->_template_class_name))
				{
					$this->_template_class_name	= self::DEFAULT_TEMPLATE_CLASS_NAME;
				}
				$this->io->setOutputAdapter($this->_template_class_name, $this->_template_skin_used);
			}

			public function callingInit()
			{
				if ($this->_is_init)
				{
					return true;
				}
				$this->init();
				$this->_is_init	= true;
			}

			/**
			 * ControllerProvider释构函数
			 *
			 *
			 *
			 */
			public function __destruct()
			{
				$this->free();
				if (DOEXEC_OB_CLEAN)
				{
					ob_end_clean();
				}
			}

			/**
			 * 设置当前的Controller与Action状态
			 *
			 * Application中自动调用，
			 * 用于处理当前URI相关。
			 * 在init()方法后调用。
			 * ！！！ 不能在Controller中调用
			 */
			public function __exec_prepare_query($query)
			{
				$this->_template_query	= $query;
			}

			/**
			 * Action方法执行完成后设置模板文件
			 *
			 * Application中自动调用，
			 * 用于与io交互设置页面模板等等，
			 * 在free()方法前调用。
			 * ！！！ 不能在Controller中调用
			 */
			public function __exec_finish_dispatch()
			{
				$template	= $this->_template_name;
				if (empty($this->_template_name))
				{
					$template	= implode('/', $this->_template_query);
				}
				elseif (!empty($this->_template_base))
				{
					$template	= $this->_template_base;
					$template  .= '/';
					$template  .= $this->_template_name;
				}
				else
				{
					$temp_arr	= $this->_template_query;
					array_pop($temp_arr);
					array_push($temp_arr, $this->_template_name);
					$template	= implode('/', $temp_arr);
				}
				$template	= preg_replace('/\/+/iU', '/', $template);
				if (preg_match('/\\'.self::DEFAULT_TEMPLATE_SUFFIX.'$/i', $template) !== 1)
				{
					$template  .= self::DEFAULT_TEMPLATE_SUFFIX;
				}
				$this->io->__get_tpl_from_control($template);
			}

			/**
			 * 设置当前使用的模板样式
			 *
			 * 可在Controller中的init()中调用函数设置
			 * 或直接在Controller定义的类中使用 $_template_skin_used 变量设定
			 *
			 * @param string $param	模板样式名称
			 */
			public function setTemplateSkinUsed($param)
			{
				$this->_template_skin_used	= $param;
			}

			/**
			 * 设置模板文件的根节点
			 *
			 * 默认为空，直接从模板配置的template_dir开始
			 *
			 * @param string $basename	根节点名称
			 */
			protected function setTemplateBase($basename)
			{
				$this->_template_base	= $basename;
			}

			/**
			 * 返回当前使用的模板文件的根节点
			 *
			 * @return string
			 */
			protected function getTemplateBase()
			{
				return $this->_template_base;
			}

			/**
			 * 返回当前使用的模板文件名
			 *
			 * @return string
			 */
			protected function setTemplateName($name)
			{
				$this->_template_name	= $name;
			}

			/**
			 * 返回当前使用的模板文件名
			 *
			 * @return string
			 */
			protected function getTemplateName()
			{
				return $this->_template_name;
			}

			/**
			 * 设置页面显示标题
			 *
			 * 设置页面显示标题
			 *
			 * @param string $title	页面标题
			 */
			protected function setPageTitle($title)
			{
				$this->io->x('pagetitle', $title);
			}

			/**
			 * 加入自己定义的CSS样式
			 *
			 * CSS样式必须存放在css目录下
			 *
			 * @param string $filename	相对于css目录的CSS文件全路径名
			 */
			protected function addcss($filename)
			{
				$this->io->_add_ext_load_css($filename);
			}

			/**
			 * 加入自己定义的JS脚本
			 *
			 * JS脚本必须存放在js目录下
			 *
			 * @param string $filename	相对于jss目录的JS文件全路径名
			 */
			protected function addscript($filename)
			{
				$this->io->_add_ext_load_script($filename);
			}

		}//~: __END_OF_CLASS__________

	}
?>