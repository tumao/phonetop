<?php

	define('CONF',	BASE.'conf'._);
	define('CORE',	BASE.'core'._);
	define('API',	CORE.'API'._);
	define('APP',	BASE.'app'._);
	define('LIB',	BASE.'lib'._);
	define('ORM',	BASE.'orm'._);
	
	/**
	 * 缓存文件目录
	 */
	define('CACHE',	'/tmp/.cache_phonetop_backend/');
	
	define('SMARTY_DIR',	LIB.'smarty-3.1.14'._.'libs'._);
	define('FIREPHP_DIR',	LIB.'FirePHPCore-0.3.2'._.'lib'._.'FirePHPCore'._);
	
	/**
	 * 上传文件目录
	 */
	define('UPLOAD_BASE_DIR',	'/mnt/samba/workspace/upload-dir');
	
	/**
	 * Smarty相关目录设置
	 *
	 * Smarty相关目录，包括模板，配置，插件，编译，缓存五个目录的配置
	 */
	define('VIEW_BASE_DIR',		BASE.'view'._);
	define('VIEW_TEMPLATE_DIR',	VIEW_BASE_DIR.'template'._);
	define('VIEW_CONFIG_DIR',	VIEW_BASE_DIR.'configs'._ );
	define('VIEW_PLUGIN_DIR',	VIEW_BASE_DIR.'plugins'._ );
	define('VIEW_COMPILE_DIR',	CACHE.'temp_c'._ );
	define('VIEW_CACHE_DIR',	CACHE.'cache_c'._);
	
?>