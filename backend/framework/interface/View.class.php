<?php
	namespace ZQFramework\Core
	{
		interface IView
		{
			/**
			 * 构造函数定义
			 *
			 * 构造函数不能带参数，
			 * 使用setInitParam()方法初始化启动参数
			 */
			public function __construct();
			
			/**
			 * 设置初始化参数方法
			 *
			 * 系统调用了构造函数生成实例对像后
			 * 自动调用该方法执行初始化操作。
			 */
			public function setInitParam($skin=NULL);
			public function assign($name, $data);
			public function fetch($tpl);
			public function display($tpl);
		}
	}
?>