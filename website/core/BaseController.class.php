<?php

	abstract class BaseController extends ZQFramework\Core\ControllerProvider
	{

		/**
		 * JSON输出格式串
		 */
		private $_json_package	= array
		(
			'code'	=> 0,			// 错误代码
			'info'	=> '',			// 错误信息
			'data'	=> array()		// 返回数据
		);

		/**
		 * 当前使用的系统
		 */
		protected $_g_os_system		= NULL;
		protected $_g_ios_breaked	= 0;

		private $_curr_menu_conf	= array();

		const USER_LOGIN_URI	= '/user/login';

		protected function __construct()
		{
			parent::__construct();
			/* 是否输出为 json 串 */
			if ($this->io->r(REQUEST_JSON_TAG_) == 'ON')
			{
				$this->io->setOutputType(OUTPUT_TYPE_JSON);
				$this->err(0, '');
			}
			else
			{
				$this->setPageTitle(LANG::SYSTEM_NAME);
				$this->addcss('global.css');
				$this->addscript('common-lib.js');
			}
			$this->_check_curr_selected_system();
			$this->_check_user_is_login();
		}

		protected function x($key, $value)
		{
			if ($this->io->isOutputJson())
			{
				$this->_json_package['data'][$key]	= $value;
				$this->io->x('data', $this->_json_package['data']);
				return true;
			}
			$this->io->x($key, $value);
		}

		protected function err($code, $info)
		{
			$this->_json_package['code']	= $code;
			$this->_json_package['info']	= $info;
			$this->io->x('code', $this->_json_package['code']);
			$this->io->x('info', $this->_json_package['info']);
		}

		private function _check_curr_selected_system()
		{
			$_session_selected_system	= $this->env->session('_current_selected_system');
			$_request_selected_system	= $this->io->r('_selected_system');
			if (!empty($_request_selected_system))
			{
				$this->_g_os_system	= $_request_selected_system;
				$this->env->session('_current_selected_system', $this->_g_os_system);
			}
			elseif (!empty($_session_selected_system))
			{
				$this->_g_os_system	= $this->env->session('_current_selected_system');
			}
			elseif ($_SERVER['HTTP_HOST'] == HOST_APPLE_SHOUJIDS)
			{
				$this->_g_os_system	= APP_SYSTEM_IOS;
			}
			$this->_g_os_system	= $this->_g_os_system != APP_SYSTEM_IOS ? APP_SYSTEM_ANDROID : APP_SYSTEM_IOS;
			if ($this->_g_os_system == APP_SYSTEM_IOS)
			{
				$_request_selected_ios_break	= $this->io->r('_selected_ios_break');
				if ($_request_selected_ios_break == 'Y')
				{
					$this->_g_ios_breaked	= 1;
					$this->env->session('_current_selected_is_breaked', $this->_g_ios_breaked);
				}
				else if ($_request_selected_ios_break == 'N')
				{
					$this->_g_ios_breaked	= 0;
					$this->env->session('_current_selected_is_breaked', $this->_g_ios_breaked);
				}
				else $this->_g_ios_breaked =  $this->env->session('_current_selected_is_breaked');
			}
			$this->_g_ios_breaked	= intval($this->_g_ios_breaked);
		}

		private function _check_user_is_login()
		{
			if ($this->env->session('UserID'))
			{
				if ($this->io->r(REQUEST_JSON_TAG_) != 'ON')
				{
					$this->_do_system_page_initialize();
				}
				return true;
			}
			if ($_SERVER['REQUEST_URI'] != self::USER_LOGIN_URI)
			{
				header('location: '.self::USER_LOGIN_URI);
			}
		}

		private function _do_system_page_initialize()
		{
			$this->uriformat	= preg_replace('/(\/)+/iu', '/', $_SERVER['REQUEST_URI'].'/');
			$this->submenuid	= '';
			$menu	= DB::ORM('Phone::Menu');
			list($id, $result)	= $menu->loadmenu();
			$list	= array();
			$selid	= NULL;
			#~: 顶部导航菜单
			while ($result[$id])
			{
				$data		= $result[$id];
				if ($this->_find_uri_curr($data['path']))
				{
					$this->_curr_menu_conf['base']	= array(
						'name'	=> $data['name'],
						'path'	=> $data['path']
					);
					$data['selected']	= true;
					$selid	= $data['id'];
				}
				else
				{
					$data['selected']	= false;
				}
				$list[]	= $data;
				$id		= $data['__tree_next'];
				if (empty($id)) break;
			}
			#~: 右侧子菜单
			$sonid	= false;
			if (isset($result[$selid]) && isset($result[$selid]['__tree_data']))
			{
				$sonid	=  $result[$selid]['__tree_data'];
			}
			$sons	= array();
			if ($sonid && isset($result[$sonid]))
			{
				$this->_sub_menu_tree($sonid, $result, $sons);
			}
			if (!isset($this->_curr_menu_conf['base']))
			{
				$this->_curr_menu_conf['base']	= array(
					'name'	=> '',
					'path'	=> ''
				);
			}
			if (!isset($this->_curr_menu_conf['child']))
			{
				$this->_curr_menu_conf['child']	= array(
					'name'	=> '',
					'path'	=> ''
				);
			}
			$submenu_json_string	= json_encode($sons);
			$this->x('menuRoot', $list);
			$this->x('submenu', $submenu_json_string);
			$this->x('navbar', $this->_curr_menu_conf);
			$this->x('selected_submenu_id', $this->submenuid);
			//~: 当前软件系统
			$this->x('_selected_system', $this->_g_os_system);
		}

		private function _find_uri_curr($uri)
		{
			$uri	= preg_replace('/(\/)+/iu', '/', $uri.'/');
			return strstr($this->uriformat, $uri) !== false;
		}

		/**
		 * 生成 二级菜单Tree格式的JSON数据
		 *
		 */
		private function _sub_menu_tree($id, &$map, &$data)
		{
			while ($map[$id])
			{
				if (empty($map[$id]['show'])) continue;
				$checked	= $this->_find_uri_curr($map[$id]['path']);
				$temp	= array
				(
					'id'		=> $map[$id]['id'],
					'text'		=> $map[$id]['name'],
					'iconCls'	=> $map[$id]['icon'],
					'checked'	=> $checked,
					'attributes'=> array
					(
						'path'	=> $map[$id]['path']
					),
					'children'	=> null
				);
				if ($checked)
				{
					$this->_curr_menu_conf['child']	= array(
						'name'	=> $map[$id]['name'],
						'path'	=> $map[$id]['path']
					);
					$this->submenuid	= $temp['id'];
				}
				if ($map[$id]['__tree_data'])
				{
					$children	= array();
					$this->_sub_menu_tree($map[$id]['__tree_data'], $map, $children);
					$temp['children']	= $children;
					$temp['iconCls']	= '';
				}
				$data[]	= $temp;
				$id	= $map[$id]['__tree_next'];
				if (empty($id)) break;
			}
		}

	}

?>