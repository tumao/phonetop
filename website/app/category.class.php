<?php

	class CategoryController extends BaseController
	{
	
		protected function init()
		{
			$this->_app_config	= require(CONF.'app-setting.php');
		}
		
		protected function free()
		{
			
		}
		
		public function indexAction()
		{
			$tagname	= $this->io->pop();
			$def_name	= strtoupper('RC_TAG_'.$tagname);
			if (!defined($def_name))
			{
				#~: $def_name	= 'RC_TAG_SOFTWARE';
				header('location: /category/software/');
				return true;
			}
			$tagname	= constant($def_name);
			$cat		= DB::ORM('Pro::Category');
			$cat->tag	= $tagname;
			$cat->root	= 0;
			$cat->loadone();
			$id	= $cat->id;
			if (empty($id))
			{
				$name	= $tagname;
				if (defined($def_name.'_NAME'))
				{
					$name	= constant($def_name.'_NAME');
				}
				$cat->name	= $name;
				$cat->tag	= $tagname;
				$cat->root	= 0;
				$cat->sort	= 0;
				$cat->admin	= '-';
				$cat->insert();
			}
			$this->x('system_cat', $this->_app_config['system']);
			$this->x('curr_root_id', $cat->id);
			$this->x('curr_tagname', $tagname);
			$this->addscript("app/category-index.js");
		}
		
		public function treeAction()
		{
			$tagname	= $this->io->pop();
			$def_name	= strtoupper('RC_TAG_'.$tagname);
			if (!defined($def_name))
			{
				$def_name	= 'RC_TAG_SOFTWARE';
			}
			$tagname	= constant($def_name);
			$cat		= DB::ORM('Pro::Category');
			list($id, $result)	= $cat->loadcategory();
			$cat->tag	= $tagname;
			$cat->root	= 0;
			$cat->loadone();
			$id	= $cat->id;
			$treedata	= array();
			if (isset($result[$id]['__tree_data']) && !empty($result[$id]['__tree_data']))
			{
				$id	= $result[$id]['__tree_data'];
				$this->parseTreeData($tagname, $id, $result, $treedata);
			}
			$this->io->setPrimitiveOutput($treedata);
		}
		
		public function saveAction()
		{
			$id		= $this->io->p('id');
			$root	= $this->io->p('root');
			$name	= $this->io->p('name');
			$cat	= DB::ORM('Pro::Category');
			// ** 是否有同名分类存在
			$cat->name	= $name;
			$cat->root	= $root;
			$cat->loadone();
			$query_id	= $cat->id;
			if (!empty($query_id) && $query_id != $id)
			{
				return $this->err(201, LANG::E_PRO_CAT_EXISTS);
			}
			// ** 新增分类
			if (empty($id))
			{
				// ** 计算排序
				$sort	= $cat->countBy(array(
					'root'	=> $root
				));
				$sort++;
				$cat->sort	= $sort;
				$cat->root	= $root;
				$cat->name	= $name;
				if (!$cat->insert())
				{
					return $this->err(1001, LANG::E_DB_OPERATION);
				}
			}
			else
			{
				$cat->id	= $id;
				$cat->load();
				$cat->name	= $name;
				if (!$cat->update())
				{
					return $this->err(1001, LANG::E_DB_OPERATION);
				}
			}
			$cat->clearBuffer();
			$this->x('id', $cat->id);
			$this->x('name', $cat->name);
		}
		
		public function delAction()
		{
			$_curr_id	= $this->io->r('id');
			if (empty($_curr_id))
			{
				return $this->err(202, LANG::E_PRO_CAT_DELID_EMPTY);
			}
			$cat	= DB::ORM('Pro::Category');
			list($id, $result)	= $cat->loadcategory();
			if (!isset($result[$_curr_id]))
			{
				return $this->err(203, LANG::E_PRO_CAT_DELID_NOT_FOUND);
			}
			$delarrid	= array();
			$this->getRelactionCatIds($_curr_id, $result, $delarrid);
			if (empty($delarrid))
			{
				return $this->err(203, LANG::E_PRO_CAT_DELID_NOT_FOUND);
			}
			if (! $cat->deleteForKey('id', $delarrid))
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
			$cat->clearBuffer();
		}
		
		/**
		 * 生成 TreeGrid 格式的JSON数据
		 *
		 */
		private function parseTreeData($tagname, $id, &$map, &$data)
		{
			while ($map[$id])
			{
				if ($tagname == RC_TAG_SOFTWARE || $tagname == RC_TAG_GAME)
				{
					if ($map[$id]['system'] != $this->_g_os_system)
					{
						$id	= $map[$id]['__tree_next'];
						if (empty($id)) break;
						continue;
					}
				}
				$temp	= array(
					'id'	=> $map[$id]['id'],
					'name'	=> $map[$id]['name'],
					'children'	=> null
				);
				if ($map[$id]['__tree_data'])
				{
					$children	= array();
					$this->parseTreeData($tagname, $map[$id]['__tree_data'], $map, $children);
					$temp['children']	= $children;
				}
				$data[]	= $temp;
				$id	= $map[$id]['__tree_next'];
				if (empty($id)) break;
			}
		}
		
		private function getRelactionCatIds($id, &$result, &$data, $next=false)
		{
			$data[]	= $id;
			if ($next && $result[$id]['__tree_next'])
			{
				$this->getRelactionCatIds($result[$id]['__tree_next'], $result, $data, true);
			}
			if ($result[$id]['__tree_data'])
			{
				$this->getRelactionCatIds($result[$id]['__tree_data'], $result, $data, true);
			}
		}
		
	}
?>