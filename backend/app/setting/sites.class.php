<?php

	class SitesController extends BaseController
	{
		
		private $site	= null;
	
		protected function init()
		{
			$this->site	= DB::ORM('Phone::Site');
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
			$list	= $this->site->listBy($query, 0, 0, '`sort` ASC, `id` ASC');
			$total	= count($list);
			$this->io->setPrimitiveOutput(array(
				'total'	=> $total,
				'rows'	=> $list
			));
		}
		
		public function formAction()
		{
			$id		= $this->io->r('id');
			$info	= $this->site->infoBy(array('id' => $id));
			var_dump( $info);exit;
			$this->io->view_assign('info', $info);
			$form	= $this->io->view_fetch('setting/sites/form.tpl');
			$this->x('formdata', $form);
		}
		
		public function saveAction()
		{
			$id		= $this->io->r('id');
			$name	= $this->io->r('name');
			$logo	= $this->io->r('logo');
			$url	= $this->io->r('url');
			$this->site->id	= $id;
			$this->site->load();
			$_r_id	= $this->site->id;
			$method	= 'update';
			if (empty($_r_id))
			{
				$maxidx	= $this->site->getMaxIndex();
				$this->site->sort	= $maxidx + 1;
				$method	= 'insert';
			}
			$this->site->name	= $name;
			$this->site->url	= $url;
			$this->site->logo	= $logo;
			if (! $this->site->$method())
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
		}
		
		public function delAction()
		{
			$id	= $this->io->r('id');
			$this->site->id	= $id;
			if (! $this->site->delete())
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
			if (! $this->site->setsorting($sortconf))
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
			$this->site->id	= $id;
			$this->site->load();
			$_r_id	= $this->site->id;
			if (empty($_r_id))
			{
				return $this->err(301, LANG::E_RC_APP_NOT_FOUND);
			}
			$flag	= $this->io->p('flag');
			if (! in_array($flag, array('is_used', 'isshow', 'islock', 'candel')))
			{
				return $this->err(301, LANG::E_RC_APP_NOT_FOUND);
			}
			$stated	= $this->site->$flag;
			$icon	= $stated ? 'no' : 'ok';
			$this->site->$flag	= $stated ? 0 : 1;
			if (!$this->site->update())
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
			return $this->x('icon', $icon);
		}

	}

?>