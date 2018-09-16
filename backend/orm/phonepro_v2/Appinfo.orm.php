<?php

	namespace ORM\phonepro_v2
	{
	
		class AppinfoORM extends \ORM
		{
			const TABLE	= 'rc_app_info';
			
			public function countDownLoads(&$info)
			{
				$query	= array(
					'day'	=> "date_format(`time`,'%Y-%m-%d')=date_format(CURRENT_TIMESTAMP,'%Y-%m-%d')",
					'week'	=> "date_format(`time`,'%Y-%u')=date_format(CURRENT_TIMESTAMP,'%Y-%u')",
					'month'	=> "date_format(`time`,'%Y-%m')=date_format(CURRENT_TIMESTAMP,'%Y-%m')"
				);
				foreach ($query as $key => $val)
				{
					$sql	= "SELECT count(*) as cc FROM `log_app_download` WHERE `app_id`={$info['id']} AND {$val}";
				}
			}
			
			public function clearCacheFor($rootid=-1, $system='android')
			{
				if ($rootid != -1)
				{
					$_cache_key	= sprintf('.app_recommend_download.%s@%s', $system, $rootid);
					$this->rdconn->del($_cache_key);
				}
				$this->rdconn->del('.cache_tag_root');
				$this->rdconn->del('.cache_cat_options');
				$this->rdconn->del('.cache_cat_map_for_rootid');
			}
		}
	}
?>