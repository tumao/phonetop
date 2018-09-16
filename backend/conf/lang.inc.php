<?php

	class LANG
	{
		
		const SYSTEM_NAME		= 'Phone桌面后台管理系统';
		
		const OPTION_ALL			= '全部';
		const OPTION_PLEASE_SELECT	= '---请选择---';
		const APP_SYSTEM_ANDROID	= '安卓系统';
		const APP_SYSTEM_IOS		= '苹果系统';
		
		const EXPLORER_ROOT_DIR		= '根目录';
		
		
		const RC_FLAG_NEW			= '最新';
		const RC_FLAG_HOT			= '最热';
		const RC_FLAG_POP			= '最流行';
		const RC_FLAG_TODAY			= '今日头条';
		const RC_FLAG_RECOMMEND		= '推荐';

		const RC_FLAG_CHARGE		= '激活付费';
		const RC_FLAG_ELITE			= '应用精选';
		const RC_FLAG_GAMECENTER	= '游戏中心';
		
		const IOS_NO_BREAK			= '末越狱';
		const IOS_BREAKED			= '越狱';
		
		################################################
		#
		# 错误提示信息
		# JSON返回中 code 与 info 对应值
		#
		##############################################################
		
		/* 100 */	const E_ACCESS_DENY				= '非法访问！';
		
		/* 101 */	const E_USER_LOGIN_USERNAME		= '登录失败，账号不存在！';
		/* 102 */	const E_USER_LOGIN_PASSWORD		= '登录失败，密码错误！';
		/* 103 */	const E_USER_NAME_PASSWORD		= '账号不存在或密码错误！';
		/* 104 */	const E_USER_CHECK_NEW_PASSWORD	= '两次输入的密码不一致！';
		/* 105 */	const E_USER_CHANGE_PASSWORD	= '修改密码失败，请稍后重试！';
		/* 106 */	const E_USER_PERMIT_DENY		= '你没有权限进行此操作！';
		/* 107 */	const E_MANAG_USERE_NOT_FOUND	= '找不到对应的账号！';
		/* 108 */	const E_USER_LOGIN_DENY			= '你的账号不允许登录，请联系管理员！';
		
		/* 201 */	const E_PRO_CAT_EXISTS			= '同级分类名称已在存在！';
		/* 202 */	const E_PRO_CAT_DELID_EMPTY		= '请选择需要删除的分类！';
		/* 203 */	const E_PRO_CAT_DELID_NOT_FOUND	= '找不到想要删除的分类！';
		
		/* 301 */	const E_RC_APP_NOT_FOUND		= '找不到对应的APP记录！';
		/* 302 */	const E_DATA_EMPTY				= '提交数据为空或不合法！';
		/* 303 */	const E_DATA_SIZE_NOT_EQUAL		= '提交数据个数不匹配！';
		
		/* 901 */	const E_MENU_NAME_IS_EMPTY		= '菜单名称不能为空！';
		/* 902 */	const E_MENU_PATH_IS_EMPTY		= '访问路径不能为空！';
		/* 903 */	const E_MENU_PARENT_NOT_FOUND	= '上级菜单不存在！';
		/* 904 */	const E_MENU_NOT_FOUND			= '菜单不存在！';
		/* 905 */	const E_MENU_DEL_ITEM_NOT_FOUND	= '没有可删除的菜单项！';
		
		###########################################################################
		# 
		# 数据库操作失败
		# 
		#################################################
		
		/* 1001 */	const E_DB_OPERATION			= '数据库操作失败';
		
		###########################################################################
		# 
		# 文件上传
		# 
		#################################################
		
		/* 1009 */	const E_UPLOAD_FILE_EXISTS		= '同名文件已经存在！';
		/* 1010 */	const E_UPLOAD_NO_FILE			= '未选择上传文件！';
		/* 1011 */	const E_UPLOAD_ERR_INI_SIZE		= '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
		/* 1012 */	const E_UPLOAD_ERR_FORM_SIZE	= '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
		/* 1013 */	const E_UPLOAD_ERR_PARTIAL		= '文件只有部分被上传！';
		/* 1014 */	const E_UPLOAD_ERR_NO_FILE		= '没有文件被上传！';
		/* 1015 */	/* 5 的错误代码缺失 */
		/* 1016 */	const E_UPLOAD_ERR_NO_TMP_DIR	= '找不到临时文件夹！';
		/* 1017 */	const E_UPLOAD_ERR_CANT_WRITE	= '文件写入失败！';
		/* 1018 */	const E_UPLOAD_STOP_BY_EXTEN	= '文件上传被扩展停止！';
		/* 1019 */	const E_UPLOAD_NO_AVAIABLE		= '上传失败，未知异常！';
		/* 1020 */	const E_UPLOAD_SAVE_FAILURE		= '保存上传文件失败，请检查目录是否有权限！';
		
		###########################################################################
		# 
		# 文件管理
		# 
		#################################################
		
		/* 1021 */	const E_EXPLORER_CREATE_DIR		= '新建目录失败！';
		/* 1022 */	const E_EXPLORER_REMOVE_DIR		= '删除目录失败！';
		/* 1023 */	const E_EXPLORER_PARENT_ERR		= '上级目录不存在！';
		/* 1024 */	const E_EXPLORER_DIRNAME_ERR	= '目录名称非法！';
		/* 1025 */	const E_EXPLORER_FILE_NO_EXISTS	= '文件不存在！';
		/* 1026 */	const E_EXPLORER_DELETE_FILE	= '删除文件失败！';
		
		
		
	}

?>