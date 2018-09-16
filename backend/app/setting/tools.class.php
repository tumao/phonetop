<?php

	class ToolsController extends BaseController
	{
		
		private $site	= null;
	
		protected function init()
		{
			$this->tool	= DB::ORM('Phone::Tool');
		}
		
		protected function free()
		{
			
		}
		
		public function indexAction()
		{
			
		}
		
		public function dataAction()
		{
			$query	= array();
			$list	= $this->tool->listBy($query, 0, 0, '`is_used` DESC, `sort` ASC, `id` ASC');
			$total	= count($list);
			$this->io->setPrimitiveOutput(array(
				'total'	=> $total,
				'rows'	=> $list
			));
		}
		
		public function formAction()
		{
			$id		= $this->io->r('id');
			$info	= $this->tool->infoBy(array('id' => $id));
			$this->io->view_assign('info', $info);
			$form	= $this->io->view_fetch('setting/tools/form.tpl');
			$this->x('formdata', $form);
		}
		
		public function saveAction()
		{
			$id		= $this->io->r('id');
			$name	= $this->io->r('name');
			$logo	= $this->io->r('logo');
			$url	= $this->io->r('url');
			$md5	= $this->io->r('md5');
			$proc	= $this->io->r('proc');
			$md5proc= $this->io->r('md5proc');
			$this->tool->id	= $id;
			$this->tool->load();
			$_r_id	= $this->tool->id;
			$method	= 'update';
			if (empty($_r_id))
			{
				$maxidx	= $this->tool->getMaxIndex();
				$this->tool->sort	= $maxidx + 1;
				$method	= 'insert';
			}
			$this->tool->name	= $name;
			$this->tool->logo	= $logo;
			$this->tool->url	= $url;
			$this->tool->md5	= $md5;
			$this->tool->proc	= $proc;
			$this->tool->md5proc= $md5proc;
			if (! $this->tool->$method())
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
		}
		
		public function delAction()
		{
			$id	= $this->io->r('id');
			$this->tool->id	= $id;
			if (! $this->tool->delete())
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
		}
		
		public function sortingAction()
		{
			$sorting	= $this->io->r('sorting');
			$arysort	= explode('|', $sorting);
			$sortconf	= array();
			foreach ($arysort as $conf)
			{
				$conf	= trim($conf);
				if (empty($conf))
				{
					continue;
				}
				list($idx, $id) = explode(':', $conf);
				$sortconf[$idx]	= $id;
			}
			if (! $this->tool->setsorting($sortconf))
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
		}
		
		public function chgAction()
		{
			$id	= $this->io->p('id');
			if (empty($id))
			{
				return $this->err(301, LANG::E_RC_APP_NOT_FOUND);
			}
			$this->tool->id	= $id;
			$this->tool->load();
			$_r_id	= $this->tool->id;
			if (empty($_r_id))
			{
				return $this->err(301, LANG::E_RC_APP_NOT_FOUND);
			}
			$flag	= $this->io->p('flag');
			if (! in_array($flag, array('is_used', 'isshow', 'islock', 'candel')))
			{
				return $this->err(301, LANG::E_RC_APP_NOT_FOUND);
			}
			$stated	= $this->tool->$flag;
			$icon	= $stated ? 'no' : 'ok';
			$this->tool->$flag	= $stated ? 0 : 1;
			if (!$this->tool->update())
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
			return $this->x('icon', $icon);
		}
		
		public function filequeryAction()
		{
			$file	= $this->io->r('file');
			$proc	= $this->io->r('proc');
			$real	= UPLOAD_BASE_DIR.$file;
			if (empty($file) || !is_file($real))
			{
				return $this->err(1025, LANG::E_EXPLORER_FILE_NO_EXISTS);
			}
			$size	= filesize($real);
			$md5	= md5_file($real);
			// ** 输出参数
			$this->x('size', $size);
			$this->x('md5', $md5);
			// ** 查询proc文件的md5值
			if (empty($proc))
			{
				$this->x('md5proc', '');
				return;
			}
			$z	= new ZipArchive();
			if ($z->open($real) !== true)
			{
				$this->x('md5proc', '');
				return;
			}
			$data	= $z->getFromName($proc);
			if ($data === false)
			{
				$this->x('md5proc', '');
				return;
			}
			$md5proc	= md5($data);
			$this->x('md5proc', $md5proc);
			$z->close();
		}

	}

?>