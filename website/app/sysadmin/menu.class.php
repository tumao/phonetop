<?php

	class MenuController extends BaseController
	{
		private $issuperadmin	= false;
	
		protected function init()
		{
			$uid	= $this->env->session('UserID');
			$user	= DB::ORM('Phone::User');
			$user->id	= $uid;
			$user->load();
			$stat	= $user->stat;
			if ($stat & 0x80)
			{
				$this->issuperadmin	= true;
			}
		}
		
		protected function free()
		{
			
		}
		
		public function indexAction()
		{
			$this->addscript("app/menu-index.js");
		}
		
		public function addAction()
		{
			if (!$this->issuperadmin)
			{
				return $this->err(106, LANG::E_USER_PERMIT_DENY);
			}
			$menu	= DB::ORM('Phone::Menu');
			//~: 值
			$root	= $this->io->p('rid');
			$name	= $this->io->p('name');
			$path	= $this->io->p('path');
			$icon	= $this->io->p('icon');
			$memo	= $this->io->p('memo');
			if (empty($name))
			{
				return $this->err(901, LANG::E_MENU_NAME_IS_EMPTY);
			}
			if (empty($path))
			{
				return $this->err(902, LANG::E_MENU_PATH_IS_EMPTY);
			}
			// ** 上级菜单是否存在，查询缓存中的数据即可
			list($id, $result)	= $menu->loadmenu();
			if ($root != 0)
			{
				if (!isset($result[$root]))
				{
					return $this->err(903, LANG::E_MENU_PARENT_NOT_FOUND);
				}
			}
			// ** 计算排序数
			$sort		= 1;
			$_curr_id	= 0;
			if (empty($root))
			{
				$_curr_id	= $id;
			}
			elseif (!empty($result[$root]['__tree_data']))
			{
				$_curr_id	= $result[$root]['__tree_data'];
			}
			if (!empty($_curr_id))
			{
				while (isset($result[$_curr_id]))
				{
					$sort ++;
					$_curr_id	= $result[$_curr_id]['__tree_next'];
					if (empty($_curr_id)) break;
				}
			}
			$menu->root	= $root;
			$menu->name	= $name;
			$menu->path	= $path;
			$menu->icon	= $icon;
			$menu->memo	= $memo;
			$menu->sort	= $sort;
			$menu->insert();
			$_menu_id	= $menu->id;
			if (empty($_menu_id))
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
			$menu->clearBuffer();
			$this->x('id', $_menu_id);
			$this->x('root', $menu->root);
			$this->x('name', $menu->name);
			$this->x('path', $menu->path);
			$this->x('icon', $menu->icon);
			$this->x('memo', $menu->memo);
		}
		
		public function editAction()
		{
			if (!$this->issuperadmin)
			{
				return $this->err(106, LANG::E_USER_PERMIT_DENY);
			}
			$menu		= DB::ORM('Phone::Menu');
			$menu->id	= $this->io->p('id');
			$menu->load();
			$name	= $menu->name;
			if (empty($name))
			{
				return $this->err(904, LANG::E_MENU_NOT_FOUND);
			}
			$menu->name	= $this->io->p('name');
			$menu->path	= $this->io->p('path');
			$menu->icon	= $this->io->p('icon');
			$menu->memo	= $this->io->p('memo');
			if (! $menu->update())
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
			$menu->clearBuffer();
		}
		
		public function delAction()
		{
			if (!$this->issuperadmin)
			{
				return $this->err(106, LANG::E_USER_PERMIT_DENY);
			}
			$_curr_id	= $this->io->r('id');
			$menu	= DB::ORM('Phone::Menu');
			list($id, $result)	= $menu->loadmenu();
			if (!isset($result[$_curr_id]))
			{
				return $this->err(904, LANG::E_MENU_NOT_FOUND);
			}
			$delarrid	= array();
			$this->getRelactionMenuIds($_curr_id, $result, $delarrid);
			if (empty($delarrid))
			{
				return $this->err(905, LANG::E_MENU_DEL_ITEM_NOT_FOUND);
			}
			if (! $menu->deleteForKey('id', $delarrid))
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
			$menu->clearBuffer();
		}
		
		/**
		 * TreeGrid 取数据接口
		 *
		 */
		public function treeAction()
		{
			$menu	= DB::ORM('Phone::Menu');
			list($id, $result)	= $menu->loadmenu();
			$treedata	= array();
			$this->parseMenuTree($id, $result, $treedata);
			$this->io->setPrimitiveOutput($treedata);
		}
		
		/**
		 * 刷新缓存
		 */
		public function refreshAction()
		{
			DB::ORM('Phone::Menu')->clearBuffer();
		}
		
		public function moveAction()
		{
			if (!$this->issuperadmin)
			{
				return $this->err(106, LANG::E_USER_PERMIT_DENY);
			}
			$_curr_id	= $this->io->r('id');
			$_position	= $this->io->r('pos');
			$_position	= $_position != 1 ? -1 : 1;
			$menu	= DB::ORM('Phone::Menu');
			list($id, $result)	= $menu->loadmenu();
			if (!isset($result[$_curr_id]))
			{
				return $this->err(904, LANG::E_MENU_NOT_FOUND);
			}
			// ** 上移
			if ($_position == -1)
			{
				//~：已经顶部
				if (empty($result[$_curr_id]['__tree_prev']))
				{
					return $this->x('reload', false);
				}
				$_prev_id		= $result[$_curr_id]['__tree_prev'];
				// ** 
				$_curr_next_id	= $result[$_curr_id]['__tree_next'];
				$_prev_prev_id	= $result[$_prev_id]['__tree_prev'];
				// **
				$result[$_curr_id]['__tree_prev']	= $_prev_prev_id;
				$result[$_curr_id]['__tree_next']	= $_prev_id;
				// ** 
				$result[$_prev_id]['__tree_prev']	= $_curr_id;
				$result[$_prev_id]['__tree_next']	= $_curr_next_id;
				// ** 
				if (!empty($result[$_prev_prev_id]))
				{
					$result[$_prev_prev_id]['__tree_next']	= $_curr_id;
				}
				if (!empty($result[$_curr_next_id]))
				{
					$result[$_curr_next_id]['__tree_prev']	= $_prev_id;
				}
			}
			else	// 下移
			{
				//~：已经底部
				if (empty($result[$_curr_id]['__tree_next']))
				{
					return $this->x('reload', false);
				}
				$_next_id		= $result[$_curr_id]['__tree_next'];
				// ** 
				$_curr_prev_id	= $result[$_curr_id]['__tree_prev'];
				$_next_next_id	= $result[$_next_id]['__tree_next'];
				// **
				$result[$_curr_id]['__tree_prev']	= $_next_id;
				$result[$_curr_id]['__tree_next']	= $_next_next_id;
				// ** 
				$result[$_next_id]['__tree_prev']	= $_curr_prev_id;
				$result[$_next_id]['__tree_next']	= $_curr_id;
				// ** 
				if (!empty($result[$_curr_prev_id]))
				{
					$result[$_curr_prev_id]['__tree_next']	= $_next_id;
				}
				if (!empty($result[$_next_next_id]))
				{
					$result[$_next_next_id]['__tree_prev']	= $_curr_id;
				}
			}
			// ** 同级分类的项全部更新排序号，防止有空或重复的序号出现
			if (empty($result[$_curr_id]['__tree_root']))
			{
				$entry	= $id;
			}
			else
			{
				$entry	= $result[$result[$_curr_id]['__tree_root']]['__tree_data'];
			}
			$sortcount	= 1;
			$sortary	= array();
			while (!empty($entry))
			{
				$sortary[]	= array(
					'id'	=> $entry,
					'sort'	=> $sortcount
				);
				$sortcount++;
				$entry	= $result[$entry]['__tree_next'];
			}
			if (!$menu->commitMenuSorting($sortary))
			{
				return $this->x('reload', false);
			}
			$menu->clearBuffer();
			$this->x('reload', true);
		}
		
		/**
		 * 生成 TreeGrid 格式的JSON数据
		 *
		 */
		private function parseMenuTree($id, &$map, &$data)
		{
			while ($map[$id])
			{
				$temp	= array(
					'id'	=> $map[$id]['id'],
					'name'	=> $map[$id]['name'],
					'icon'	=> $map[$id]['icon'],
					'path'	=> $map[$id]['path'],
					'show'	=> $map[$id]['show'],
					'memo'	=> $map[$id]['memo'],
					'children'	=> null
				);
				if ($map[$id]['__tree_data'])
				{
					$children	= array();
					$this->parseMenuTree($map[$id]['__tree_data'], $map, $children);
					$temp['children']	= $children;
				}
				$data[]	= $temp;
				$id	= $map[$id]['__tree_next'];
				if (empty($id)) break;
			}
		}
		
		private function getRelactionMenuIds($id, &$result, &$data, $next=false)
		{
			$data[]	= $id;
			if ($next && $result[$id]['__tree_next'])
			{
				$this->getRelactionMenuIds($result[$id]['__tree_next'], $result, $data, true);
			}
			if ($result[$id]['__tree_data'])
			{
				$this->getRelactionMenuIds($result[$id]['__tree_data'], $result, $data, true);
			}
		}
		
		
	}

?>