<?php
	/**
	 * LANG
	 *
	 * @package Framework\Lang
	 *
	 * @author 李文强 <liwenqiang@shoujids.com>
	 * @copyright 朱雀网络
	 * @version 1.0
	 */
	namespace ZQFramework\Lang
	{
		
		class LANG
		{
			
			/**
			 * APP目录未定义
			 */
			const APPLICTION_DIR_NOT_DEFINED	= 'APP目录未定义！';
		
			/**
			 * APP目录不存在
			 */
			const APPLICTION_DIR_NOT_EXISTS		= 'APP目录不存在！';
			
			/**
			 * 找不到指定的页面
			 */
			const PAGE_NOT_FOUND				= '找不到指定的页面！';
			
			
			###############################################################################
			#
			# 模板配置相关提示信息
			#
			#############################################################################################################################
			
			/**
			 * 模板样式名称错误
			 */
			const VIEW_SKIN_NAME_ERROR			= '模板样式名称错误！';
			
			/**
			 * VIEW_TEMPLATE_DIR常量未定义
			 */
			const VIEW_TEMPLATE_DIR_NOT_DEFINED	= 'VIEW_TEMPLATE_DIR常量未定义！';
			
			/**
			 * VIEW_COMPILE_DIR常量未定义
			 */
			const VIEW_COMPILE_DIR_NOT_DEFINED	= 'VIEW_COMPILE_DIR常量未定义！';
			
			/**
			 * VIEW_CACHE_DIR_NOT_DEFINED常量未定义
			 */
			const VIEW_CACHE_DIR_NOT_DEFINED	= 'VIEW_CACHE_DIR_NOT_DEFINED常量未定义！';
			
			/**
			 * 模板文件目录不存在
			 */
			const VIEW_TEMPLATE_DIR_NOT_FOUND	= '模板文件目录不存在！';
			
			/**
			 * 模板编译目录不存在
			 */
			const VIEW_COMPILE_DIR_NOT_FOUND	= '模板编译目录不存在！';
			
			/**
			 * 模板编译目录没有读写权限
			 */
			const VIEW_COMPILE_DIR_NOT_WRITABLE	= '模板编译目录没有读写权限！';
			
			/**
			 * 模板缓存目录不存在
			 */
			const VIEW_CACHE_DIR_NOT_FOUND		= '模板缓存目录不存在！';
			
			/**
			 * 模板缓存目录没有读写权限
			 */
			const VIEW_CACHE_DIR_NOT_WRITABLE	= '模板缓存目录没有读写权限！';
			
			
			###############################################################################
			#
			# 数据库操作相关提示信息
			#
			#############################################################################################################################

			const DB_ORM_DIRECTORY_IS_NOT_DEFINED	= '没有找到ORM目录的定义！';
			const DB_ORM_DIRECTORY_IS_NOT_FOUND		= 'ORM定义的目录不存在！';
			const DB_CONFIG_IS_NOT_DEFINED			= '没有找到DB_CONFIG数据库配置文件定义！';
			const DB_CONFIG_IS_NOT_FOUND			= 'DB_CONFIG定义的数据库配置文件不存在！';
			const DB_CONFIG_USER_IS_EMPTY			= '数据库连接配置中，用户名不能为空！';
			const DB_CONFIG_DBNAME_IS_EMPTY			= '数据库连接配置中，库名不能为空！';
			const DB_LOAD_DIRKEY_IS_EMPTY			= 'Load-ORM的dirkey值为空！';
			const DB_LOAD_DIRKEY_SIZE_ERROR			= 'Load-ORM的dirkey值格式错误！';
			const DB_LOAD_DIRKEY_LINK_NOT_FOUND		= 'Load-ORM的dirkey值中linker无对应配置！';
			const DB_LOAD_DIRECTORY_NOT_FOUND		= '对应DB配置的ORM目录不存在！';
			const DB_LOAD_FILE_NOT_FOUND			= '对应DB配置的ORM文件不存在！';
			const DB_LOAD_CLASS_NOT_FOUND			= '找不到Load-ORM对应的类！';
			const DB_LOAD_CLASS_NOT_EXTENDS_DBORM	= 'Load-ORM对应的类必须继承自 \ZQFramework\Core\DBORM 类！';
			const DB_ORM_CONSTANT_TABLE_NOT_DEFINED	= 'Load-ORM类中的TABLE常量值未定义！';
			const DB_ORM_CONSTANT_TABLE_NOT_FOUND	= 'Load-ORM类中的TABLE常量值定义的表不存在！';
			const DB_INSERT_PARAM_DATA_ERROR		= 'DB::insert()时参数data错误，必须为数组并且不能为空！';
			const DB_INSERT_FIELD_IS_EMPTY			= 'DB::insert()时插入的字段为空！';
			const DB_QUERY_CONDITION_IS_EMPTY		= 'DB单记录操作时查询条件为空！';
			const DB_ORM_ROW_EXEC_ERROR				= 'DB单记录操作失败！';
			const DB_ORM_FIELD_NOT_FOUND			= '表中字段不存在！';
			const DB_ORM_PARAM_MUST_BE_ARRAY		= '参数必须为非空数组！';
		}
	}
?>