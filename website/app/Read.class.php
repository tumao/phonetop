<?php

	class ReadControl extends ABaseControl
	{
		
		public function init()
		{
			
		}
		
		public function free()
		{
		
		}
		
		public function site()
		{
			$orm	= SiteORM::getInstance();
			$where	= "`is_used`=1 AND `isshow`=1";
			$order	= "`sort` ASC, `id` ASC";
			$list	= $orm->listByQuery($where, $order, $this->_param_size, $this->_sql_offset, array($this, 'makeSiteOutput'));
			echo	json_encode($list);
		}
		
		public function tool()
		{
			$orm	= ToolORM::getInstance();
			$where	= "`is_used`=1 AND `isshow`=1";
			$order	= "`sort` ASC, `id` ASC";
			$list	= $orm->listByQuery($where, $order, $this->_param_size, $this->_sql_offset, array($this, 'makeToolOutput'));
			echo	json_encode($list);
		}
		
		public function makeSiteOutput($idx, $row)
		{
			return array
			(
				'id'	=> $row['id'],
				'name'	=> $row['name'],
				'logo'	=> $row['logo'],
				'url'	=> $row['url'],
				'lock'	=> $row['islock'],
				'del'	=> $row['candel'],
				'sort'	=> $idx
			);
		}
		
		public function makeToolOutput($idx, $row)
		{
			return array
			(
				'id'	=> $row['id'],
				'name'	=> $row['name'],
				'logo'	=> $row['logo'],
				'zip'	=> array(
					'url'	=> $row['url'],
					'md5'	=> $row['md5']
				),
				'proc'	=> array(
					'name'	=> $row['proc'],
					'md5'	=> $row['md5proc']
				),
				'lock'	=> $row['islock'],
				'del'	=> $row['candel'],
				'sort'	=> $idx
			);
		}
	}

?>