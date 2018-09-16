<?php

	namespace ORM\phonepro_v2
	{
	
		class AppDownloadORM extends \ORM
		{
			const TABLE	= 'log_app_download';
			
			public function countDownLoads(&$info)
			{
				if (empty($info['id']))
				{
					$info['day_real_downloads']		= '';
					$info['week_real_downloads']	= '';
					$info['month_real_downloads']	= '';
					return true;
				}
				$query	= array(
					'day'	=> "date_format(`time`,'%Y-%m-%d')=date_format(CURRENT_TIMESTAMP,'%Y-%m-%d')",
					'week'	=> "date_format(`time`,'%Y-%u')=date_format(CURRENT_TIMESTAMP,'%Y-%u')",
					'month'	=> "date_format(`time`,'%Y-%m')=date_format(CURRENT_TIMESTAMP,'%Y-%m')"
				);
				foreach ($query as $key => $val)
				{
					$field			= $key.'_real_downloads';
					$info[$field]	= $this->countByQuery("`app_id`={$info['id']} AND {$val}");
				}
			}
		}
	}
?>