<?php

	/**
	 * 最新
	 */
	define('RC_FLAG_NEW',		0x0001);
	
	/**
	 * 最热
	 */
	define('RC_FLAG_HOT',		0x0002);
	
	/**
	 * 最流行
	 */
	define('RC_FLAG_POP',		0x0004);
	
	/**
	 * 今日头条
	 */
	define('RC_FLAG_TODAY',		0x0006);
	
	/**
	 * 推荐
	 */
	define('RC_FLAG_RECOMMEND',	0x0008);

	/**
	 * 激活付费
	 */
	define('RC_FLAG_CHARGE', 0x0020);

	/**
	 * 应用精选
	 */
	define('RC_FLAG_ELITE', 0x0040);

	/**
	 * 游戏中心
	 */
	define('RC_FLAG_GAMECENTER', 0x0080);

	
	####################################################################
	#
	# 操作系统标记
	#
	#################################################################################
	
	/**
	 * Android
	 */
	define('APP_SYSTEM_ANDROID',	'android');
	define('HOST_ANDROID_SHOUJIDS',	'192.168.1.15:81');
	
	/**
	 * iOS
	 */
	define('APP_SYSTEM_IOS',		'ios');
	define('HOST_APPLE_SHOUJIDS',	'192.168.1.15:83');
	
?>