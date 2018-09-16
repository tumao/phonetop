<?php
	/**
	 * StreamProvider
	 *
	 * 输入输出流，处理输入参数及程序输出
	 * 通过IView接口来实现页面输出
	 *
	 * @package Framework\Core
	 *
	 * @author 李文强 <liwenqiang@shoujids.com>
	 * @copyright 朱雀网络
	 * @version 1.0
	 */
	namespace ZQFramework\Core
	{
		if (!class_exists('Smarty'))
		{
			require(SMARTY_DIR.'Smarty.class.php');
		}
		class SmartyTemplate extends \Smarty implements IView
		{
			/**
			 * 构造函数
			 *
			 * 实现IView接口，调用Smarty类构造函数。
			 */
			public function __construct()
			{
				parent::__construct();
			}

			/**
			 * 初始化参数
			 *
			 * 实现IView接口方法
			 * 初始化模板相关目录
			 *
			 * @param string $skin	模板样式名称
			 */
			public function setInitParam($skin=NULL)
			{
				if (empty($skin))
				{
					$skin	= DEFAULT_SKIN;
				}
				elseif (preg_match('/^[a-z]+[a-z0-9]*$/i', $skin) !== 1)
				{
					throw new ZQException(\ZQFramework\Lang\LANG::VIEW_SKIN_NAME_ERROR);
				}
				/* 验证是否已定义模板相关常量 */
				if (!defined('VIEW_TEMPLATE_DIR'))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::VIEW_TEMPLATE_DIR_NOT_DEFINED);
				}
				if (!defined('VIEW_COMPILE_DIR'))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::VIEW_COMPILE_DIR_NOT_DEFINED);
				}
				if (!defined('VIEW_CACHE_DIR'))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::VIEW_CACHE_DIR_NOT_DEFINED);
				}
				/* 验证模板目录是否存在 */
				if (!is_dir(VIEW_TEMPLATE_DIR.$skin))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::VIEW_TEMPLATE_DIR_NOT_FOUND);
				}
				$this->setTemplateDir(array($skin => VIEW_TEMPLATE_DIR.$skin));
				/* 验证模板配置与插件文件目录，若存在则设置对应目录 */
				/* 项目中不一定需要到此两目录，所以目录不需要作强制校验 */
				if (defined('VIEW_CONFIG_DIR') && is_dir(VIEW_CONFIG_DIR.$skin))
				{
					$this->setConfigDir(array($skin => VIEW_CONFIG_DIR.$skin));
				}
				if (defined('VIEW_PLUGIN_DIR') && is_dir(VIEW_PLUGIN_DIR.$skin))
				{
					$this->setPluginDir(array($skin => VIEW_PLUGIN_DIR.$skin));
				}
				/* 验证编译目录是否存在，不存在时则自动生成 */
				if (!is_dir(VIEW_COMPILE_DIR))
				{
					if (!mkdir(VIEW_COMPILE_DIR, 0755, true))
					{
						throw new ZQException(\ZQFramework\Lang\LANG::VIEW_COMPILE_DIR_NOT_FOUND);
					}
				}
				if (!is_dir(VIEW_COMPILE_DIR.$skin))
				{
					if (!mkdir(VIEW_COMPILE_DIR.$skin, 0755))
					{
						throw new ZQException(\ZQFramework\Lang\LANG::VIEW_COMPILE_DIR_NOT_FOUND);
					}
				}
				if (!is_writable(VIEW_COMPILE_DIR.$skin))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::VIEW_COMPILE_DIR_NOT_WRITABLE);
				}
				$this->setCompileDir(VIEW_COMPILE_DIR.$skin);
				/* 验证缓存目录是否存在，不存在时则自动生成 */
				if (!is_dir(VIEW_CACHE_DIR))
				{
					if (!mkdir(VIEW_CACHE_DIR, 0755, true))
					{
						throw new ZQException(\ZQFramework\Lang\LANG::VIEW_CACHE_DIR_NOT_FOUND);
					}
				}
				if (!is_dir(VIEW_CACHE_DIR.$skin))
				{
					if (!mkdir(VIEW_CACHE_DIR.$skin, 0755))
					{
						throw new ZQException(\ZQFramework\Lang\LANG::VIEW_CACHE_DIR_NOT_FOUND);
					}
				}
				if (!is_writable(VIEW_CACHE_DIR.$skin))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::VIEW_CACHE_DIR_NOT_WRITABLE);
				}
				$this->setCacheDir(VIEW_CACHE_DIR.$skin);
			}
		}
	}

?>