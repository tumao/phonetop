<?php

	/* 项目中定义FRAMEWORK路径，多个项目可同用一份FRAMEWORK文件 */
	if (!defined('FRAMEWORK'))
	{
		exit('FRAMEWORK NOT DEFINED');
	}
	if (!defined('_'))
	{
		define('_', DIRECTORY_SEPARATOR);
	}
	define('FRAMEWORK_CONF', FRAMEWORK.'conf'._);
	define('FRAMEWORK_CORE', FRAMEWORK.'core'._);
	define('FRAMEWORK_LANG', FRAMEWORK.'lang'._);

	/* 接口目录 */
	define('FRAMEWORK_INTERFACE',	FRAMEWORK.'interface'._);

	require(FRAMEWORK_CORE.'Singleton.php');

	/* 默认配置文件 */
	foreach (glob(FRAMEWORK_CONF.'*.inc.php') as $_file_conf_framework)
	{
		require($_file_conf_framework);
	}

	/* 接口文件 */
	foreach (glob(FRAMEWORK_INTERFACE.'*.class.php') as $_file_interface_framework)
	{
		require($_file_interface_framework);
	}

	/* 核心类文件 */
	foreach (glob(FRAMEWORK_CORE.'*.class.php') as $_file_core_framework)
	{
		require($_file_core_framework);
	}

	/* 语言包，设置输出编码 */
	defined('LANG_CHARSET') or define('LANG_CHARSET', 'zh_CN.UTF-8');

	if (is_file(FRAMEWORK_LANG.LANG_CHARSET.'.php'))
	{
		require(FRAMEWORK_LANG.LANG_CHARSET.'.php');
		list($lang, $charset)	= explode('.', LANG_CHARSET);
		header('Content-Type: text/html; charset='.$charset);
	}

?>