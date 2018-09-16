<?php

	/**
	 * 缓存HASH表名
	 */
	define('CACHE_TABLE',		'.cache');

	/**
	 * 缓存菜单名
	 */
	define('MENU_CACHE',		'menu');
	
	/**
	 * 分类缓存
	 */
	define('CATEGORY_CACHE',	'category');
	
	/**
	 * 分类Tree数组与KV对数组缓存
	 */
	define('CATEGORY_ROOT_CACHE',	'category_root_map');
	define('CATEGORY_TREE_CACHE',	'category_tree_map');
	define('CATEGORY_ITEM_CACHE',	'category_item_map');
	
	/**
	 * 文件管理中的路径名
	 *
	 * Tree中只回传ID不带TEXT
	 * ID值不能带 /等特殊符号
	 */
	define('EXPLORER_CACHE_DIR_ID',	'.explorer-dir-ids');
?>