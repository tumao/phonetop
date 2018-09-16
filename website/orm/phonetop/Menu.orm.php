<?php

	namespace ORM\phonetop
	{
		class MenuORM extends \ORM
		{
			const TABLE	= 'menu_catalogues';
			
			public function loadmenu()
			{
				$s	= $this->rdconn->hget(CACHE_TABLE, MENU_CACHE);
				if (!empty($s))
				{
					return unserialize($s);
				}
				$list	= $this->listBy(NULL, 0, 0, '`root` ASC, `sort` ASC');
				list($id, $result)	= \ZQFramework\Core\Common::ParseIdRootTree($list);
				$pack	= array($id, $result);
				$this->rdconn->hset(CACHE_TABLE, MENU_CACHE, serialize($pack));
				return $pack;
			}
			
			public function clearBuffer()
			{
				$this->rdconn->hdel(CACHE_TABLE, MENU_CACHE);
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
		}//~: _____END_OF_CLASS___________________________
	}
?>