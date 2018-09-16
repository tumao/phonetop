<?php

	namespace ORM\phonetop
	{
	
		class CategoryORM extends \ORM
		{
			const TABLE	= 'rc_category';
			
			private $_map_tree	= array();
			private $_map_item	= array();
			private $_map_root	= array();
			
			public function loadcategory()
			{
				$s	= $this->rdconn->hget(CACHE_TABLE, CATEGORY_CACHE);
				if (!empty($s))
				{
					return unserialize($s);
				}
				$list	= $this->listBy(NULL, 0, 0, '`root` ASC, `sort` ASC, `id` ASC');
				list($id, $result)	= \ZQFramework\Core\Common::ParseIdRootTree($list);
				$pack	= array($id, $result);
				$this->rdconn->hset(CACHE_TABLE, CATEGORY_CACHE, serialize($pack));
				return $pack;
			}
			
			public function getMapRoot()
			{
				$string	= $this->rdconn->hget(CACHE_TABLE, CATEGORY_ROOT_CACHE);
				if (empty($string))
				{
					$this->_map_cache();
					$string	= serialize($this->_map_root);
					$string	= $this->rdconn->hset(CACHE_TABLE, CATEGORY_ROOT_CACHE, $string);
					return $this->_map_root;
				}
				return unserialize($string);
			}
			
			public function clearBuffer()
			{
				$this->rdconn->hdel(CACHE_TABLE, CATEGORY_CACHE);
				$this->rdconn->hdel(CACHE_TABLE, CATEGORY_ROOT_CACHE);
				$this->rdconn->hdel(CACHE_TABLE, CATEGORY_TREE_CACHE);
				$this->rdconn->hdel(CACHE_TABLE, CATEGORY_ITEM_CACHE);
			}
			
			public function commitMenuSorting($data)
			{
				$sql	= "UPDATE `".self::TABLE."` SET `sort`=:sort WHERE `id`=:id";
				$sth	= $this->connection->prepare($sql);
				$this->connection->beginTransaction();
				foreach ($data as $x)
				{
					$sth->bindParam(':sort', $x['sort'], \PDO::PARAM_INT);
					$sth->bindParam(':id',   $x['id'],   \PDO::PARAM_INT);
					$sth->execute();
				}
				return $this->connection->commit();
			}
			
			/**
			 * UI::Tree格式数组
			 */
			public function getTreeBy($tag)
			{
				return $this->_get_map_data($this->_map_tree, CATEGORY_TREE_CACHE, $tag);
			}
			
			/**
			 * UI::Tree格式数组
			 *
			 * param: array $tags
			 */
			public function getTreeForTags($tags, $system=NULL)
			{
				if (empty($tags) || !is_array($tags))
				{
					return false;
				}
				$option	= $this->getMapRoot();
				$result	= array();
				foreach ($tags as $tag)
				{
					if (isset($option[$tag]))
					{
						$catree	= $this->getTreeBy($tag);
						if ($tag == RC_TAG_SOFTWARE || $tag == RC_TAG_GAME)
						{
							if (isset($catree[$system]))
							{
								$catree	= $catree[$system];
							}
						}
						$result[]	= array(
							'id'	=> $option[$tag]['id'],
							'text'	=> $option[$tag]['text'],
							'children'	=> $catree
						);
					}
				}
				return $result;
			}
			
			/**
			 * Key => value 对数组
			 */
			public function getItemBy($tag)
			{
				return $this->_get_map_data($this->_map_item, CATEGORY_ITEM_CACHE, $tag);
			}
			
			/**
			 * 格式化的分类数据缓存
			 */
			private function _map_cache()
			{
				if (!empty($this->_map_root))
				{
					return true;
				}
				list($id, $result) = $this->loadcategory();
				while (isset($result[$id]))
				{
					if ($result[$id]['root'] == '0')
					{
						$this->_map_root[$result[$id]['tag']]	= array(
							'id'	=> $result[$id]['id'],
							'text'	=> $result[$id]['name']
						);
						$this->_map_item[$result[$id]['tag']]	= array();
						$this->_map_tree[$result[$id]['tag']]	= array();
						$sonid	= $result[$id]['__tree_data'];
						if (!empty($sonid))
						{
							$this->_parse_for_tag(
								$this->_map_item[$result[$id]['tag']],
								$this->_map_tree[$result[$id]['tag']],
								$result, $sonid, $result[$id]['tag']
							);
						}
					}
					$id	= $result[$id]['__tree_next'];
				}
			}
			
			private function _get_map_data(&$data, $key, $tag)
			{
				if (!empty($data))
				{
					if (!isset($data[$tag]))
					{
						return false;
					}
					return $data[$tag];
				}
				if (!$this->rdconn->hexists(CACHE_TABLE, $key))
				{
					$this->_map_cache();
				}
				else
				{
					$string	= $this->rdconn->hget(CACHE_TABLE, $key);
					$data	= unserialize($string);
				}
				if (empty($data) || !isset($data[$tag]))
				{
					return false;
				}
				return $data[$tag];
			}
			
			private function _parse_for_tag(&$item, &$tree, &$result, $id, $tag=NULL)
			{
				while (isset($result[$id]))
				{
					$temp	= array(
						'id'	=> $result[$id]['id'],
						'text'	=> $result[$id]['name'],
						'children'	=> array()
					);
					if ($result[$id]['__tree_data'])
					{
						$children	= array();
						$this->_parse_for_tag($item, $children, $result, $result[$id]['__tree_data']);
						$temp['children']	= $children;
					}
					if ($tag == RC_TAG_SOFTWARE || $tag == RC_TAG_GAME)
					{
						$tree[$result[$id]['system']][]	= $temp;
						$item[$result[$id]['system']][$result[$id]['id']]	= $result[$id]['name'];
					}
					else
					{
						$tree[]	= $temp;
						$item[$result[$id]['id']]	= $result[$id]['name'];
					}
					$id	= $result[$id]['__tree_next'];
				}
			}
			
		}//~: _____END_OF_CLASS___________________________
	}
?>