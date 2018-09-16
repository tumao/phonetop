<?php
	
	/**
	 * 语言包字符集设置
	 */
	define('LANG_CHARSET',		'zh_CN.UTF-8');
	
	/**
	 * 默认控制器名
	 */
	define('DEFAULT_CONTROL',	'default');
	
	/**
	 * 默认方法名
	 */
	define('DEFAULT_ACTION',	'index');
	
	/**
	 * 模板样式名称
	 *
	 * 增加此参数配置多套模板
	 */
	define('DEFAULT_SKIN',		'default');
	
	/**
	 * 提交字段名，带对应值时，输出返回JSON数组
	 */
	define('REQUEST_JSON_TAG_',	'json_tag__');
	
	/**
	 * 数据库配置文件名
	 *
	 * 数据库配置文件，{ENV}替换为对应 __ENV_GLOBAL 值
	 * 对于与服务器相关的配置使用不同的配置文件
	 */
	define('DB_CONFIG',		CONF.'service'._.'db-config-{ENV}.inc.php');
	
	/**
	 * firephp日志记录开关 （bool)
	 *
	 * 开发时设置为 true 可以输出相关调试信息到 FirePHP 栏中。
	 */
	define('FIREPHP_LOG_FLAG',	true);
	
	/**
	 * 关闭非正常的输出信息
	 *
	 * 开发时设置为 false 可看到一些自定义的调试信息。
	 */
	define('DOEXEC_OB_CLEAN',	false);
	
	/**
	 * 上传文件访问路径
	 *
	 * 不要以 '/' 结尾
	 */
	// define('UPLOAD_URL', 'http://192.168.1.15:888');
	define('UPLOAD_URL', '/upload');
	
?>