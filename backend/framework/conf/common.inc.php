<?php
	/**
	 * Framework中需要用到的预定义值
	 *
	 * 若需要重新定义，在项目的配置中重新定义
	 * 不要在framework的配置中更改
	 */
	
	if (!defined('__ENV_GLOBAL'))
	{
		/**
		 * 当前使用的环境相关设置
		 *
		 * 使用production是为生产环境
		 */
		define('__ENV_GLOBAL', 'production');
	}

	if (!defined('LANG_CHARSET'))
	{
		/**
		* 语言包字符集设置
		*/
		define('LANG_CHARSET',		'zh_CN.UTF-8');
	}
	
	if (!defined('DEFAULT_CONTROL'))
	{
		/**
		 * 默认控制器名
		 */
		define('DEFAULT_CONTROL',	'default');
	}

	if (!defined('DEFAULT_ACTION'))
	{
		/**
		 * 默认方法名
		 */
		define('DEFAULT_ACTION',	'index');
	}
	
	if (!defined('DEFAULT_SKIN'))
	{
		/**
		 * 模板样式名称
		 *
		 * 增加此参数配置多套模板
		 */
		define('DEFAULT_SKIN',	'default');
	}
	
	if (!defined('FIREPHP_LOG_FLAG'))
	{
		/**
		 * firephp日志记录开关 （bool)
		 */
		define('FIREPHP_LOG_FLAG',	true);
	}
	
	if (!defined('CACHE'))
	{
		/**
		 * 缓存文件目录
		 */
		define('CACHE',	'/tmp/'.md5(__FILE__.$_SERVER['HTTP_HOST'])._);
	}
	
	if (!defined('DOEXEC_OB_CLEAN'))
	{
		/**
		 * 关闭非正常的输出信息
		 */
		define('DOEXEC_OB_CLEAN',	true);
	}

?>