<?php
	
	namespace ORM\phonetop
	{
	
		class SiteORM extends \ORM
		{
			const TABLE	= 'setting_sites';
			
			public function setsorting($sort)
			{
				$conn	= $this->getConnection();
				$query	= "UPDATE `".self::TABLE."` SET `sort`=:sort WHERE `id`=:id";
				$sth	= $conn->prepare($query);
				$conn->beginTransaction();
				foreach ($sort as $idx => $id)
				{
					$sth->bindParam('sort', $idx, \PDO::PARAM_INT);
					$sth->bindParam('id', $id, \PDO::PARAM_INT);
					$sth->execute();
				}
				return $conn->commit();
			}
			
			public function getMaxIndex()
			{
				$sql	= "SELECT max(`sort`) FROM `".self::TABLE."`";
				$result	= $this->getConnection()->query($sql);
				$info	= $result->fetch();
				if (empty($info)) return 0;
				$maxidx	= array_pop($info);
				return $maxidx;
			}
		}
	}

?>