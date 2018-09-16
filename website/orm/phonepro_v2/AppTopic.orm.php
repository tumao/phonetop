<?php

	namespace ORM\phonepro_v2
	{
	
		class AppTopicORM extends \ORM
		{
			const TABLE	= 'app_topic';
			
			public function get_app_selected($arr_app_id)
			{
				if (empty($arr_app_id))
				{
					return array();
				}
				$arr_app_id	= explode(',', $arr_app_id);
				$valified	= array();
				foreach ($arr_app_id as $id)
				{
					$id	= intval($id);
					if (empty($id))
					{
						continue;
					}
					$valified[]	= $id;
				}
				if (empty($valified))
				{
					return array();
				}
				$where	= "SELECT `id`, `name`, `root`, `cid` FROM `rc_app_info` WHERE `is_used`=1 AND `id` IN (" . implode(',', $valified) . ")";
				$conn	= $this->getConnection();
				$result	= $conn->query($where);
				$list	= array();
				while ($row = $result->fetch())
				{
					$list[]	= $row;
				}
				return $list;
			}
			
		}
	}

?>