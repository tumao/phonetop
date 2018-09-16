<?php
	/**
	 * StreamProvider
	 *
	 * 输入输出流，处理输入参数及程序输出
	 * 通过IView接口来实现页面输出
	 *
	 * @package Framework\Core
	 *
	 * @author 李文强 <liwenqiang@shoujids.com>
	 * @copyright 朱雀网络
	 * @version 1.0
	 */
	namespace ZQFramework\Core
	{
		class StreamProvider extends Singleton
		{

			private $env		= NULL;
			private $common		= NULL;

			/**
			 * 输出的类型
			 *
			 * 如 HTML，JSON等等
			 */
			private $_output_type	= NULL;

			/**
			 * 输出的变量值数组
			 *
			 * 使用x()方法设置的变量值
			 * 可替换模板变量等等。
			 */
			private $_output_data	= array();

			/**
			 * 输出实现对象
			 *
			 * 实现IView接口的输出实例对象。
			 */
			private $opInstance		= NULL;

			private $_current_load_css		= array();
			private $_current_load_script	= array();

			/**
			 * 是否使用主框架显示页面方式
			 */
			private $_is_show_frame			= true;

			/**
			 * 当前使用的模板文件名
			 */
			private $_output_template		= NULL;

			/**
			 * 实现对象原生函数前缀
			 *
			 * 使用此前缀可直接调用$opInstance中原生函数
			 * 如调用 smarty中的fetch()函数，使用 view_fetch()
			 */
			const PREFFIX_FOR_CALLING_VIEW_HANDLER	= 'view_';

			/**
			 * 默认主框架TPL文件名
			 */
			const TPL_MAIN_FRAME	= 'mainFrame.tpl';
			/**
			 * 默认主框架TPL中BODY部分模板名
			 */
			const TAG_FRAME_BODY	= '_tpl_framebody';

			/**
			 * StreamProvider构造函数
			 *
			 *
			 */
			protected function __construct()
			{
				$this->env		= Env::getInstance();
				$this->common	= Common::getInstance();
			}

			public function setOutputAdapter($classname, $param=false)
			{
				$reflection	= new \ReflectionClass($classname);
				$this->opInstance	= $reflection->newInstance();
				$this->opInstance->setInitParam($param);
			}

			/**
			 * 获取$_REQUEST参数对应值
			 *
			 * 返回$_REQUEST中对应指定参数名的值
			 *
			 * @param string $mkey		请求的参数名
			 * @param string $filter	输入参数过滤器
			 * @return mixed $value		输出的参数值（可能为数组）
			 */
			public function r($mkey, $filter=INPUT_FILTER_ALL)
			{
				if (!isset($_REQUEST[$mkey]))
				{
					return false;
				}
				return $this->common->parseDataByFilter($_REQUEST[$mkey], $filter);
			}

			/**
			 * 获取$_GET参数对应值
			 *
			 * 返回$_GET中对应指定参数名的值
			 *
			 * @param string $mkey		请求的参数名
			 * @param string $filter	输入参数过滤器
			 * @return mixed $value		输出的参数值（可能为数组）
			 */
			public function g($mkey, $filter=INPUT_FILTER_ALL)
			{
				if (!isset($_GET[$mkey]))
				{
					return false;
				}
				return $this->common->parseDataByFilter($_GET[$mkey], $filter);
			}

			/**
			 * 获取$_POST参数对应值
			 *
			 * 返回$_POST中对应指定参数名的值
			 *
			 * @param string $mkey		请求的参数名
			 * @param string $filter	输入参数过滤器
			 * @return mixed $value		输出的参数值（可能为数组）
			 */
			public function p($mkey, $filter=INPUT_FILTER_ALL)
			{
				if (!isset($_POST[$mkey]))
				{
					return false;
				}
				return $this->common->parseDataByFilter($_POST[$mkey], $filter);
			}

			/**
			 * 获取URI串中的参数值
			 *
			 * 返回URI串中除了controller与action外的参数值
			 * 参数值取出后不能再重新取同一个值，只能取下一个
			 * 若已经没了参数，则返回 false，使用 === 比较。
			 *
			 * @param string $filter	输入参数过滤器
			 * @return string $value	输出的参数值
			 */
			public function pop($filter=INPUT_NORMAL)
			{
				$data	= $this->env->param_pop();
				return $this->common->parseDataByFilter($data, $filter);
			}

			/**
			 * 设置使用框架显示模式或包含方式
			 *
			 * @param bool $flag	是否标志
			 */
			public function setIsShowFrame($flag=true)
			{
				$this->_is_show_frame	= empty($flag) ? false : true;
			}

			/**
			 * 设置输出的类型
			 *
			 * 输出类型，如HTML页面，或JSON字串
			 *
			 * @param string $type	输出的类型
			 */
			public function setOutputType($type=OUTPUT_TYPE_HTML)
			{
				$this->_output_type	= $type;
			}

			/**
			 * 输出是否为JSON格式数据
			 *
			 */
			public function isOutputJson()
			{
				return $this->_output_type == OUTPUT_TYPE_JSON;
			}

			/**
			 * 设置输出变量
			 *
			 *
			 *
			 */
			public function x($name, $mixed)
			{
				$this->_output_data[$name]	= $mixed;
			}

			/**
			 * 原生的输出数据
			 *
			 * 一些特殊的输出需要直接设置
			 */
			public function setPrimitiveOutput($data)
			{
				$this->_output_data	= $data;
			}

			/**
			 * 显示页面
			 *
			 *
			 */
			public function output()
			{
				if (DOEXEC_OB_CLEAN)
				{
					ob_clean();
				}
				if ($this->_output_type == OUTPUT_TYPE_JSON)
				{
					echo json_encode($this->_output_data);
				}
				elseif ($this->_output_type == OUTPUT_TYPE_TEXT)
				{
					echo $this->_output_data;
				}
				else
				{
					foreach ($this->_output_data as $key => $val)
					{
						$this->opInstance->assign($key, $val);
					}
					if (empty($this->_current_load_css))
					{
						$this->_current_load_css	= false;
					}
					if (empty($this->_current_load_script))
					{
						$this->_current_load_script	= false;
					}
					$this->opInstance->assign('_current_load_css', $this->_current_load_css);
					$this->opInstance->assign('_current_load_script', $this->_current_load_script);
					$template	= $this->getFileTemplate();
					if ($this->_is_show_frame)
					{
						$this->showframe($template);
					}
					else
					{
						$this->opInstance->display($template);
					}
				}
				ob_flush();
			}

			/**
			 * 返回页面内容
			 *
			 */
			public function fetch($template)
			{
				foreach ($this->_output_data as $key => $val)
				{
					$this->opInstance->assign($key, $val);
				}
				return $this->opInstance->fetch($template);
			}

			/**
			 * 使用主框架方式显示页面
			 *
			 * @param string $template	模板文件名称
			 */
			private function showframe($template)
			{
				$this->opInstance->assign('_tpl_framebody', $template);
				$this->opInstance->display(self::TPL_MAIN_FRAME);
			}

			/**
			 * 当使用的模板文件
			 *
			 * @retrun string	模板文件名称
			 */
			private function getFileTemplate()
			{
				return $this->_output_template;
			}

			/**
			 * __call
			 *
			 */
			public function __call($method, $param=NULL)
			{
				if (preg_match('/^'.self::PREFFIX_FOR_CALLING_VIEW_HANDLER.'([a-z]+)$/i', $method) === 1)
				{
					$calling	= substr($method, strlen(self::PREFFIX_FOR_CALLING_VIEW_HANDLER));
					return call_user_func_array(array($this->opInstance, $calling), $param);
				}
				return false;
			}

			/**
			 * 加入自己定义的CSS样式
			 *
			 * CSS样式必须存放在css目录下
			 * Controller中封装此方法名为 addcss()
			 * 在Controller的实现类中调用 addcss() 即可！
			 *
			 * @param string $filename	相对于css目录的CSS文件全路径名
			 */
			public function _add_ext_load_css($filename)
			{
				if (in_array($filename, $this->_current_load_css))
				{
					return true;
				}
				$this->_current_load_css[]	= $filename;
			}

			/**
			 * 加入自己定义的JS脚本
			 *
			 * JS脚本必须存放在js目录下
			 * Controller中封装此方法名为 addscript()
			 * 在Controller的实现类中调用 addscript() 即可！
			 *
			 * @param string $filename	相对于jss目录的JS文件全路径名
			 */
			public function _add_ext_load_script($filename)
			{
				if (in_array($filename, $this->_current_load_script))
				{
					return true;
				}
				$this->_current_load_script[]	= $filename;
			}

			public function __get_tpl_from_control($name)
			{
				$this->_output_template	= $name;
			}

		}//~: __END_OF_CLASS__________
	}
?>