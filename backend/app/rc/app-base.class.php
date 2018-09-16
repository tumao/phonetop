<?php

	abstract class AppBaseController extends BaseController
	{
		/**
		 * 分类实例
		 */
		protected $cat	= NULL;
		
		/**
		 * 根分类ID值
		 */
		protected $rootid	= 0;
	
		/**
		 * key => value 对
		 */
		protected $_cat_options	= array();
		
		/**
		 * jquery-easyui的tree树值
		 */
		protected $_cat_treeopt	= array();
		
		protected $_app_config	= array();
		
		
		protected function init()
		{
			if (empty($this->_tagname))
			{
				throw new ProException('TAGNAME NOT DEFINED');
			}
			$this->cat	= DB::ORM('Phone::Category');
			list($id, $result)	= $this->cat->loadcategory();
			while (isset($result[$id]))
			{
				if ($result[$id]['tag'] == $this->_tagname)
				{
					$this->rootid	= $id;
					break;
				}
				$id	= $result[$id]['__tree_next'];
			}
			if (empty($this->rootid))
			{
				throw new ProException('ROOT ID NOT DEFINED');
			}
			$this->_cat_treeopt	= $this->cat->getTreeBy($this->_tagname);
			$this->_cat_options	= $this->cat->getItemBy($this->_tagname);
			$this->_app_config	= require(CONF.'app-setting.php');
		}
		
		protected function free()
		{
			
		}
		
		public function indexAction()
		{
			$this->x('_curr_tagname', $this->_tagname);
			$this->setTemplateBase('rc/app');
			$this->setTemplateName('index');
			$this->addscript("app/rc-application.js");
			$_cat_system_tag	= $this->_g_os_system;
			if ($this->_g_os_system == 'ios' && $this->_g_ios_breaked == 1)
			{
				$_cat_system_tag	.= '-bj';
			}
			$_cat_treeopt	= array(
				array(
					'id'	=> 0,
					'text'	=> LANG::OPTION_ALL,
					'children'	=> array()
				)
			);
			if (isset($this->_cat_treeopt[$_cat_system_tag]))
			{
				foreach ($this->_cat_treeopt[$_cat_system_tag] as $idx => $tree)
				{
					$_cat_treeopt[]	= $tree;
				}
			}
			$this->x('_cat_tree', json_encode($_cat_treeopt));
			$_cat_appstat	= array
			(
				array(
					'id'	=> 0,
					'text'	=> LANG::OPTION_ALL
				),
				// array(
				// 	'id'	=> RC_FLAG_NEW,
				// 	'text'	=> LANG::RC_FLAG_NEW,
				// ),
				// array(
				// 	'id'	=> RC_FLAG_HOT,
				// 	'text'	=> LANG::RC_FLAG_HOT
				// ),
				// array(
				// 	'id'	=> RC_FLAG_POP,
				// 	'text'	=> LANG::RC_FLAG_POP
				// ),
				array(
					'id'	=> RC_FLAG_TODAY,
					'text'	=> LANG::RC_FLAG_TODAY
				),
				array(
					'id'	=> RC_FLAG_RECOMMEND,
					'text'	=> LANG::RC_FLAG_RECOMMEND
				),
				array(
					'id'	=> RC_FLAG_CHARGE,
					'text'	=> LANG::RC_FLAG_CHARGE
				),
				array(
					'id'	=> RC_FLAG_GAMECENTER,
					'text'	=> LANG::RC_FLAG_GAMECENTER
				)
			);
			$this->x('_cat_stat', json_encode($_cat_appstat));
			if ($this->_cat_options === false || !isset($this->_cat_options[$_cat_system_tag]))
			{
				$_cat_options = array();
			}
			else
			{
				$_cat_options	= $this->_cat_options[$_cat_system_tag];
			}
			$this->x('_selected_system', $this->_g_os_system);
			$this->x('_selected_isbreak', $this->_g_ios_breaked);
			$this->x('system_cat', $this->_app_config['system']);
			$this->x('_cat_configure', json_encode($_cat_options));
			$this->x('_app_conf_star', json_encode($this->_app_config['star']));
			$this->x('_app_conf_system', json_encode($this->_app_config['system']));
			// ** 选操作系统时的分类联动
			$this->x('_cat_options_for_system', json_encode($this->_cat_treeopt));
		}
		
		public function dataAction()
		{
			// ** easyui-pagination 中传过来的参数
			$rows	= $this->io->p('rows');
			$page	= $this->io->p('page');
			// ** 处理参数，防止非数字参数值
			$page	= intval($page);
			$page	= $page < 1 ? 1 : $page;
			$rows	= intval($rows);
			$rows	= $rows < 10 ? 10 : $rows;
			// ** 查询数据库
			$query['root']	= $this->rootid;
			// ** 条件查询
			$query['system']	= $this->_g_os_system;
			$query['is_break']	= $this->_g_ios_breaked;
			$time_from	= $this->io->r('from');
			$time_to	= $this->io->r('to');
			if (!empty($time_from))
			{
				$query['_'][]	= "`update_time`>='{$time_from}'";
			}
			if (!empty($time_to))
			{
				$query['_'][]	= "`update_time`<='{$time_to}'";
			}
			$cid	= $this->io->r('cid');
			if (!empty($cid))
			{
				$query['cid']	= $cid;
			}
			$stat	= $this->io->r('stat');
			if (!empty($stat))
			{
				$query['_'][]	= " `stat` & {$stat} ";
			}
			$name	= $this->io->r('keyword');
			$name	= trim($name);
			$app	= DB::ORM('Phone::Appinfo');
			$set	= false;
			if (!empty($name))
			{
				require(LIB.'QueryBuilder.class.php');
				$qconf	= net\koogix\library\mysql\QueryBuilder::init()->make($name, 'name');
				$query['_'][]	= $qconf['where'];
				$set	= $qconf['query'].", LENGTH(`name`) AS `len`";
				$order	= "`{$qconf['field']}` ASC, `len` ASC, `update_time` DESC, `stat` DESC, `id` DESC";
/*				$query['_'][]	= "`name` like '%{$name}%'";
				// ** Query Order 按照 相似度排序
				$set	= "CASE WHEN `name` LIKE '{$name}%' THEN 1
								WHEN `name` LIKE '%{$name}%' THEN 2
							END AS `likey`, LENGTH(`name`) AS `len`
						";
				$order	= "`likey` ASC, `len` ASC, `update_time` DESC, `stat` DESC, `id` DESC";
*/			}
			else
			{
				$order	= "`update_time` DESC, `stat` DESC, `id` DESC";
			}
			// ** 排序处理
			if (isset($_REQUEST['sort']))
			{
				$keysort	= $this->io->r('sort');
				$ordsort	= $this->io->r('order');
				if ($keysort == 'dc_total_base')
				{
					$order	= "`dc_total_base` {$ordsort}, {$order}";
				}
				else if ($keysort == 'do_sort') 
				{
					$order = "`do_sort` {$ordsort}, {$order}";
				}
				else if ($keysort == 'isused')
				{
					$order	= "`is_used` {$ordsort}, {$order}";
				}
				else if ($keysort == 'istoday')
				{
					$set	= "CASE WHEN `stat` & ".RC_FLAG_TODAY." > 0 THEN 2
									WHEN `stat` & ".RC_FLAG_TODAY." < 1 THEN 1
								END AS `fieldsort`
							";
					$order	= "`fieldsort` {$ordsort}, {$order}";
				}
				else if ($keysort == 'recommend')
				{
					$set	= "CASE WHEN `stat` & ".RC_FLAG_RECOMMEND." > 0 THEN 2
									WHEN `stat` & ".RC_FLAG_RECOMMEND." < 1 THEN 1
								END AS `fieldsort`
							";
					$order	= "`fieldsort` {$ordsort}, {$order}";
				}
				// else if ($keysort == 'isnew')
				// {
				// 	$set	= "CASE WHEN `stat` & ".RC_FLAG_NEW." > 0 THEN 2
				// 					WHEN `stat` & ".RC_FLAG_NEW." < 1 THEN 1
				// 				END AS `fieldsort`
				// 			";
				// 	$order	= "`fieldsort` {$ordsort}, {$order}";
				// }
				// else if ($keysort == 'ishot')
				// {
				// 	$set	= "CASE WHEN `stat` & ".RC_FLAG_HOT." > 0 THEN 2
				// 					WHEN `stat` & ".RC_FLAG_HOT." < 1 THEN 1
				// 				END AS `fieldsort`
				// 			";
				// 	$order	= "`fieldsort` {$ordsort}, {$order}";
				// }
				// else if ($keysort == 'ispopular')
				// {
				// 	$set	= "CASE WHEN `stat` & ".RC_FLAG_POP." > 0 THEN 2
				// 					WHEN `stat` & ".RC_FLAG_POP." < 1 THEN 1
				// 				END AS `fieldsort`
				// 			";
				// 	$order	= "`fieldsort` {$ordsort}, {$order}";
				// }
				//付费激活
				else if ( $keysort == 'ischarge')
				{
					$set	= "CASE WHEN `stat` & ".RC_FLAG_CHARGE." > 0 THEN 2
									WHEN `stat` & ".RC_FLAG_CHARGE." < 1 THEN 1
								END AS `fieldsort`
							";
					$order	= "`fieldsort` {$ordsort}, {$order}";
				}
				else if ( $keysort == 'iselite')
				{
					$set	= "CASE WHEN `stat` & ".RC_FLAG_ELITE." > 0 THEN 2
									WHEN `stat` & ".RC_FLAG_ELITE." < 1 THEN 1
								END AS `fieldsort`
							";
					$order	= "`fieldsort` {$ordsort}, {$order}";
				}
				else if ( $keysort == 'isgamecenter')
				{
					$set	= "CASE WHEN `stat` & ".RC_FLAG_GAMECENTER." > 0 THEN 2
									WHEN `stat` & ".RC_FLAG_GAMECENTER." < 1 THEN 1
								END AS `fieldsort`
							";
					$order	= "`fieldsort` {$ordsort}, {$order}";
				}
			}
			$total	= $app->countBy($query);
			$offset	= ($page - 1) * $rows;
			if ($set)
			{
				$list	= $app->selectionListBy($set, $query, $rows, $offset, $order);
			}
			else
			{
				$list	= $app->listBy($query, $rows, $offset, $order);
			}
			$size	= count($list);
			for ($i = 0 ; $i < $rows; $i++)
			{
				if (isset($list[$i]))
				{
					$id		= $list[$i]['id'];
					$true	= '1:' . $id;
					$false	= '0:' . $id;
					$list[$i]['istoday']	= ($list[$i]['stat'] & RC_FLAG_TODAY) ? $true : $false;
					// $list[$i]['isnew']		= ($list[$i]['stat'] & RC_FLAG_NEW) ? $true : $false;
					// $list[$i]['ishot']		= ($list[$i]['stat'] & RC_FLAG_HOT) ? $true : $false;
					// $list[$i]['ispopular']	= ($list[$i]['stat'] & RC_FLAG_POP) ? $true : $false;

					$list[$i]['ischarge']	= ($list[$i]['stat'] & RC_FLAG_CHARGE) ? $true : $false;
					$list[$i]['iselite']	= ($list[$i]['stat'] & RC_FLAG_ELITE) ? $true : $false;
					$list[$i]['isgamecenter']	= ($list[$i]['stat'] & RC_FLAG_GAMECENTER) ? $true : $false;
					$list[$i]['recommend']	= ($list[$i]['stat'] & RC_FLAG_RECOMMEND) ? $true : $false;
					$list[$i]['isused']		= $list[$i]['is_used'] ? $true : $false;
				}
				else
				{
					$list[]	= array();
				}
			}
			$this->io->setPrimitiveOutput(array(
				'total'	=> $total,
				'rows'	=> $list
			));
		}
		
		/** 
		 * 编辑修改记录
		 *
		 */
		public function infoAction()
		{
			$_cat_system_tag	= $this->_g_os_system;
			if ($this->_g_os_system == 'ios' && $this->_g_ios_breaked == 1)
			{
				$_cat_system_tag	.= '-bj';
			}
			$id		= $this->io->r('id');
			$app	= DB::ORM('Phone::Appinfo');
			$info	= $app->infoBy(array('id' => $id));
			$info['list_pic']	= array();
			if (!empty($info['pictures']))
			{
				$info['list_pic']	= explode("\n", $info['pictures']);
			}
			if (!empty($info['memo']))
			{
				$info['memo']	= str_replace('<br />', "\n", $info['memo']);
			}
			if (!empty($info['update_content']))
			{
				$info['update_content']	= str_replace('<br />', "\n", $info['update_content']);
			}
			// ** 下载次数
			$down	= DB::ORM('Phone::AppDownload');
			$down->countDownLoads($info);
			// ** 分类
			$cat_options[0]	= LANG::OPTION_PLEASE_SELECT;
			if ($this->_cat_options && isset($this->_cat_options[$_cat_system_tag]))
			{
				foreach ($this->_cat_options[$_cat_system_tag] as $key => $value)
				{
					$cat_options[$key]	= $value;
				}
			}
			$this->io->view_assign('_cat_options', $cat_options);
			// ** 系统
			$app_system[0]	= LANG::OPTION_PLEASE_SELECT;
			foreach ($this->_app_config['system'] as $key => $value)
			{
				$app_system[$key]	= $value;
			}
			$this->io->view_assign('_app_system', $app_system);
			$this->io->view_assign('_app_selected_system', $this->_g_os_system);
			// ** 星级
			$this->io->view_assign('_app_stars', $this->_app_config['star']);
			// ** 语言
			$this->io->view_assign('_app_lang', $this->_app_config['lang']);
			// ** 越狱
			$this->io->view_assign('_ios_breaking', array(
				'0'	=> LANG::IOS_NO_BREAK,
				'1'	=> LANG::IOS_BREAKED
			));
			$this->io->view_assign('_is_breaked', $this->_g_ios_breaked);
			// ** 获取显示的页面
			$this->io->view_assign('info', $info);
			$form	= $this->io->view_fetch('rc/app/form.tpl');
			// ** 设置返回值
			$this->x('formdata', $form);
		}
		
		/** 
		 * 保存数据
		 */
		public function saveAction()
		{
			$app	= DB::ORM('Phone::Appinfo');
			$id		= $this->io->p('id');
			if (!empty($id))
			{
				$app->id	= $id;
				$app->load();
				$name	= $app->name;
				if ($name === false)
				{
					return $this->err(301, LANG::E_RC_APP_NOT_FOUND);
				}
				$savehandler	= 'update';
			}
			else
			{
				$savehandler	= 'insert';
				$app->stat		= RC_FLAG_NEW;
			}
			// var_dump( $_POST);exit;
			foreach ($_POST as $key => $value)
			{
				if ($key == 'id') continue;
				if ($key == 'pic')
				{
					$pictures	= '';
					if (!empty($_POST['pic']))
					{
						$pic	= $this->io->p('pic');
						$pictures	= implode("\n", $pic);
					}
					$app->pictures	= $pictures;
					continue;
				}
				if (in_array($key, array('app_size', 'point', 'dc_total_base','do_sort','dc_month_from', 'dc_week_from', 'dc_day_from')))
				{
					$app->$key	= intval($this->io->p($key));
				}
				else
				{
					$app->$key	= $this->io->p($key);
				}
			}
			$app->update_time	= date('Y-m-d');
			$app->root			= $this->rootid;
			if (!$app->$savehandler())
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
			$app->clearCacheFor($this->rootid, $this->_g_os_system);
		}
		
		public function delAction()
		{
			$id	= $this->io->p('id');
			if (empty($id))
			{
				return $this->err(301, LANG::E_RC_APP_NOT_FOUND);
			}
			$app	= DB::ORM('Phone::Appinfo');
			$app->id	= $id;
			if (!$app->delete())
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
			$app->clearCacheFor($this->rootid, $this->_g_os_system);
		}
		
		public function filequeryAction()
		{
			$file	= $this->io->r('file');
			$real	= UPLOAD_BASE_DIR.$file;
			if (empty($file) || !is_file($real))
			{
				return $this->err(1025, LANG::E_EXPLORER_FILE_NO_EXISTS);
			}
			$size	= filesize($real);
			$md5	= md5(file_get_contents($real));
			// * 解包取包类类名等信息
			$configure	= array();
			if ($this->_g_os_system == APP_SYSTEM_ANDROID)
			{
				require(LIB.'apktool'._.'Apktool.class.php');
				$apktool	= Apktool::getInstance();
				$configure	= $apktool->getApkInfo($real);
			}
			if (empty($configure))
			{
				$configure	= array(
					'package'	=> array(
						'name'	=> '',
						'versionCode' => '',
						'versionName' => ''
					),
					'application'	=> array(
						'label'	=> '',
						'icon'	=> ''
					),
					'launchable-activity'	=> array(
						'name'	=> ''
					)
				);
			}
			// ** 输出参数
			$this->x('size', $size);
			$this->x('md5', $md5);
			$this->x('package', $configure['package']['name']);
			$this->x('classname', $configure['launchable-activity']['name']);
			$this->x('version', $configure['package']['versionName']);
			$this->x('app_version', $configure['package']['versionCode']);
			$this->x('app_name', $configure['application']['label']);
			// ** 读取logo图标
			$iconpath	= array('url' => '', 'path' => '');
			if (!empty($configure['application']['icon']))
			{
				$iconfile	= $configure['application']['icon'];
				foreach (array('320', '240', '160', '120') as $dpi)
				{
					if (isset($configure['application-icon-'.$dpi]))
					{
						$iconfile	= $configure['application-icon-'.$dpi];
						break;
					}
				}
				$iconpath	= $apktool->getFileFrom($iconfile);
			}
			$this->x('icon', $iconpath);
		}
		
		public function chgAction()
		{
			$id	= $this->io->p('id');
			if (empty($id))
			{
				return $this->err(301, LANG::E_RC_APP_NOT_FOUND);
			}
			$app	= DB::ORM('Phone::Appinfo');
			$app->id	= $id;
			$app->load();
			$appid	= $app->id;
			if (empty($appid))
			{
				return $this->err(301, LANG::E_RC_APP_NOT_FOUND);
			}
			$flag	= $this->io->p('flag');
			$icon	= 'ok';
			if ($flag == 'used')
			{
				$isused	= $app->is_used;
				if ($isused)
				{
					$icon	= 'no';
				}
				$app->is_used	= $isused ? 0 : 1;
				if (!$app->update())
				{
					return $this->err(1001, LANG::E_DB_OPERATION);
				}
				return $this->x('icon', $icon);
			}
			$stat	= $app->stat;
			$set	= -1;
			if ($flag == 'recommend')
			{
				if ($stat & RC_FLAG_RECOMMEND)
				{
					$set	= $stat & ~ RC_FLAG_RECOMMEND;
					$icon	= 'no';
				}
				else
				{
					$set	= $stat | RC_FLAG_RECOMMEND;
				}
			}
			elseif ($flag == 'today')
			{
				if ($stat & RC_FLAG_TODAY)
				{
					$set	= $stat & ~ RC_FLAG_TODAY;
					$icon	= 'no';
				}
				else
				{
					$set	= $stat | RC_FLAG_TODAY;
				}
			}
			// elseif ($flag == 'new')
			// {
			// 	if ($stat & RC_FLAG_NEW)
			// 	{
			// 		$set	= $stat & ~ RC_FLAG_NEW;
			// 		$icon	= 'no';
			// 	}
			// 	else
			// 	{
			// 		$set	= $stat | RC_FLAG_NEW;
			// 	}
			// }
			// elseif ($flag == 'hot')
			// {
			// 	if ($stat & RC_FLAG_HOT)
			// 	{
			// 		$set	= $stat & ~ RC_FLAG_HOT;
			// 		$icon	= 'no';
			// 	}
			// 	else
			// 	{
			// 		$set	= $stat | RC_FLAG_HOT;
			// 	}
			// }
			// elseif ($flag == 'popular')
			// {
			// 	if ($stat & RC_FLAG_POP)
			// 	{
			// 		$set	= $stat & ~ RC_FLAG_POP;
			// 		$icon	= 'no';
			// 	}
			// 	else
			// 	{
			// 		$set	= $stat | RC_FLAG_POP;
			// 	}
			// }
			elseif ($flag == 'charge')
			{
				if ($stat & RC_FLAG_CHARGE)
				{
					$set	= $stat & ~ RC_FLAG_CHARGE;
					$icon	= 'no';
				}
				else
				{
					$set	= $stat | RC_FLAG_CHARGE;
				}
			}
			elseif ($flag == 'elite')
			{
				if ($stat & RC_FLAG_ELITE)
				{
					$set	= $stat & ~ RC_FLAG_ELITE;
					$icon	= 'no';
				}
				else
				{
					$set	= $stat | RC_FLAG_ELITE;
				}
			}
			elseif ($flag == 'gamecenter')
			{
				if ($stat & RC_FLAG_GAMECENTER)
				{
					$set	= $stat & ~ RC_FLAG_GAMECENTER;
					$icon	= 'no';
				}
				else
				{
					$set	= $stat | RC_FLAG_GAMECENTER;
				}
			}

			if ($set == -1)
			{
				return true;
			}
			$app->stat	= $set;
			if (!$app->update())
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
			$this->x('icon', $icon);
			$app->clearCacheFor($this->rootid, $this->_g_os_system);
		}
		
		public function setdownloadAction()
		{
			$id		= $this->io->r('id');
			$number	= $this->io->r('num');
			$app	= DB::ORM('Phone::Appinfo');
			$app->id	= $id;
			$app->load();
			$appid	= $app->id;
			if (empty($appid))
			{
				return $this->err(301, LANG::E_RC_APP_NOT_FOUND);
			}
			$app->dc_total_base	= $number;
			if (!$app->update())
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
		}

	}

?>