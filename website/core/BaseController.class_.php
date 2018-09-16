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