<?php
	
	/**
	 * 数据库连接相配置
	 *
	 * 其中一级键名为 ZQFramework\Core\DBProvider::ORM() 方法参数中的 LINKER 值
	 * 二级的键名分别使用 
	 * ~ host	服务器地址（默认为 localhost）
	 * ~ port	服务器端口（默认为 3306）
	 * ~ user	连接的用户名
	 * ~ password	对应用户的密码
	 * ~ dbname		数据库名称（当Load()中DB缺省时使用此值）
	 * ~ options	附加的连接参数配置数组（使用pdo_mysql选项）
	 */
	
	return array
	(
		/* oa 库，账号登录 */
		'Phone'	=> array
		(
			'host'	=> '127.0.0.1',
			'port'	=> 3306,
			'user'	=> 'root',
			'password'	=> '123456',
			'dbname'	=> 'phonetop',
			'options'	=> array()
		),
		// phone master 手机大师
		'Pro'	=> array
		(
					'host'	=> '127.0.0.1',
					'port'	=> 3306,
					'user'	=> 'developer',
					'password'	=> 'zq@123456',
					'dbname'	=> 'phonepro_v2',
					'options'	=> array()
		)
	);

?>
