<?php

	/**
	 * 输入参数过滤器（无）
	 *
	 * 设置为此值时，输入参数原样返回，不作相关转制操作。
	 * 除了此值外，以下的几个 INPUT_*（除INPUT_FILTER_ALL外） 
	 * 可以使用 | 操作设置同时使用的几个过滤选项。
	 */
	define('INPUT_NORMAL',	0);

	/**
	 * 输入参数过滤器（URI）
	 *
	 * 使用url_decode()处理
	 */
	define('INPUT_URI',		0x0001);
	
	/**
	 * 输入参数过滤（SCRIPT）
	 *
	 * JavaScript语句标签过滤，如<script>标签转换为&lt;script&gt;
	 */
	define('INPUT_SCRIPT',	0x0002);
	
	/**
	 * 输入参数过滤（SQL）
	 * 
	 * SQL语句中变量的相关过滤，如单引号等。
	 */
	define('INPUT_SQL',		0x0004);
	
	
	define('INPUT_HTML',	0x0008);
	
	/**
	 * 输入参数全部类型的过滤
	 *
	 * 所有已定义过滤类型的组合，作默认参数使用
	 */
	define('INPUT_FILTER_ALL',	INPUT_URI | INPUT_SCRIPT | INPUT_SQL | INPUT_HTML);
	
	/**
	 * 输出参数相关设置
	 *
	 * 程序只能有一种输入方式
	 * 如输出为页面（HTML），
	 * 或者为JSON字符串，
	 * 或者其它。
	 */
	
	/**
	 * 输出参数设置（页面）
	 */
	define('OUTPUT_TYPE_HTML',	'html');
	
	/**
	 * 输出参数设置（JSON字串）
	 */
	define('OUTPUT_TYPE_JSON',	'json');
	
	/**
	 * 输出参数设置（文本字串）
	 */
	define('OUTPUT_TYPE_TEXT',	'text');

?>