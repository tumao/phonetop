<?php
	/**
	 * 引导文件
	 *
	 *
	 */

	define('__ENV_GLOBAL', 'develop');
	define('_', DIRECTORY_SEPARATOR);
	define('BASE', __DIR__._);
	
	/**
	 * 加载项目的配置文件
	 */
	foreach (glob(BASE.'conf'._.'*.inc.php') as $_file_configure)
	{
		require($_file_configure);
	}
	
	/**
	 * 先加载配置文件夹后才能加载framework，
	 * 否则项目配置将被framework默认配置覆盖。
	 * 类文件需要先加载framework的中类才能加载项目的类，
	 * 否则项目中的类找不到继承的父类。
	 */
	define('FRAMEWORK', BASE.'framework/');
	require(FRAMEWORK.'loader.php');
	// define('FRAMEWORK', 'phar://'.LIB.'framework.phar/');
	// include('lib/framework.phar');
	
	/**
	 * 加载项目核心类文件
	 */
	foreach (glob(CORE.'*.class.php') as $_file_classes)
	{
		require($_file_classes);
	}
?>