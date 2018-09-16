<?php

	class QueryControl extends ABaseControl
	{
		private $_id_filter	= array();
		
		public function init()
		{
			if (isset($_REQUEST['id']))
			{
				$arrid	= $_REQUEST['id'];
				if (!is_array($arrid))
				{
					$arrid	= explode(',', $arrid);
				}
				foreach ($arrid as $id)
				{
					$id	= intval($id);
					if (empty($id)) continue;
					$this->_id_filter[]	= $id;
				}
			}
		}
		
		public function free()
		{
			
		}
		
		public function site()
		{
			$orm	= SiteORM::getInstance();
			$where	= "`is_used`=1";
			if (!empty($this->_id_filter))
			{
				$where .= " AND `id` NOT IN (".implode(',', $this->_id_filter).")";
			}
			$order	= "`sort` ASC, `id` ASC";
			$list	= $orm->listByQuery($where, $order, $this->_param_size, $this->_sql_offset, array($this, 'makeSiteOutput'));
			echo	json_encode($list);
		}
		
		public function tool()
		{
			$orm	= ToolORM::getInstance();
			$where	= "`is_used`=1";
			if (!empty($this->_id_filter))
			{
				$where .= " AND `id` NOT IN (".implode(',', $this->_id_filter).")";
			}
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
				'url'	=> $row['url']
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
				)
			);
		}
	}

?>