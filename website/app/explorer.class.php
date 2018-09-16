<?php

	class ExplorerController extends BaseController
	{
		private $rdconn		= NULL;
		
		private $baseDir	= '';
		private $currdir	= '';
		private $realdir	= '';
		
		protected function init()
		{
			$this->rdconn	= RedisConn::getConnection();
			$this->baseDir	= UPLOAD_BASE_DIR;
			$curr_dir_id	= $this->io->r('id');
			if (empty($curr_dir_id))
			{
				$curr_dir_id	= md5('/');
				$this->currdir	= '/';
			}
			else
			{
				$this->currdir	= $this->rdconn->hget(EXPLORER_CACHE_DIR_ID, $curr_dir_id);
			}
			if (empty($this->currdir))
			{
				$this->currdir	= '/';
			}
			if (! preg_match('/\/$/', $this->currdir))
			{
				$this->currdir	.= '/';
			}
			$this->currdir	= preg_replace('/(\.)*\//iu', '/', $this->currdir);
			$this->realdir	= preg_replace('/(\/)+/iu', '/', $this->baseDir.$this->currdir);
		}
		
		protected function free()
		{
			
		}
		
		public function indexAction()
		{
			
		}
		
		/** 
		 * 目录
		 *
		 */
		public function dirAction()
		{
			$listdir	= array();
			$di	= new DirectoryIterator($this->realdir);
			foreach ($di as $fileinfo)
			{
				if (!$fileinfo->isDot() && $fileinfo->isDir())
				{
					$name	= $fileinfo->getFilename();
					if ($name == '_thumb') continue;
					$path	= $this->currdir.$name.'/';
					$keyid	= md5($path);
					$listdir[]	= array(
						'id'	=> $keyid,
						'text'	=> $name,
						'state'	=> 'closed'
					);
					$this->rdconn->hset(EXPLORER_CACHE_DIR_ID, $keyid, $path);
				}
			}
			if ($this->currdir == '/')
			{
				$keyid	= md5('/');
				$treedata	= array(
					array(
						'id'	=> $keyid,
						'text'	=> LANG::EXPLORER_ROOT_DIR,
						'state'	=> 'open',
						'children'	=> $listdir
					)
				);
				$this->rdconn->hset(EXPLORER_CACHE_DIR_ID, $keyid, '/');
			}
			else
			{
				$treedata	= $listdir;
			}
			$this->io->setPrimitiveOutput($treedata);
		}
		
		/**
		 * 文件列表
		 *
		 */
		public function fileAction()
		{
			$list	= array();
/*			$di	= new DirectoryIterator($this->realdir);
			foreach ($di as $fileinfo)
			{
				if (!$fileinfo->isDot() && $fileinfo->isFile() && !$fileinfo->isLink())
				{
					$name	= $fileinfo->getFilename();
					$path	= $this->currdir.$name.'/';
					$keyid	= md5($path);
					$list[]	= array(
						'name'	=> $name,
						'path'	=> $this->currdir.$name,
						'url'	=> UPLOAD_URL.$this->currdir.$name,
						'icon'	=> $this->_file_ext($name)
					);
				}
			}
*/
/**/
 #* 使用 ls -St 排序文件：最新上传的排在最前 *#
			$command	= "ls -St " . $this->realdir;
			$result		= array();
			$code		= -1;
			exec($command, $result, $code);
			foreach ($result as $name)
			{
				if (is_dir($this->realdir.$name) || is_link($this->realdir.$name))
				{
					continue;
				}
				$path	= $this->currdir.$name.'/';
				$keyid	= md5($path);
				$list[]	= array(
					'name'	=> $name,
					'path'	=> $this->currdir.$name,
					'url'	=> UPLOAD_URL.$this->currdir.$name,
					'icon'	=> $this->_file_ext($name)
				);
			}
			
/**/
			$this->x('files', $list);
		}
		
		/**
		 * 新建目录
		 *
		 */
		public function mkdirAction()
		{
			if (!is_dir($this->realdir))
			{
				return $this->err(1023, LANG::E_EXPLORER_PARENT_ERR);
			}
			$name	= $this->io->p('name');
			$name	= trim($name);
			if (empty($name) || !preg_match('/^[a-z0-9\._-]+$/i', $name) || preg_match('/^(\.)+$/', $name))
			{
				return $this->err(1024, LANG::E_EXPLORER_DIRNAME_ERR);
			}
			if (! @mkdir($this->realdir.$name))
			{
				return $this->err(1021, LANG::E_EXPLORER_CREATE_DIR);
			}
			$keyid	= md5($this->currdir.$name.'/');
			$this->x('id', $keyid);
			$this->x('name', $name);
		}
		
		/**
		 * 删除目录
		 *
		 */
		public function rmdirAction()
		{
			if (! @rmdir($this->realdir))
			{
				return $this->err(1022, LANG::E_EXPLORER_REMOVE_DIR);
			}
		}
		
		/**
		 * 上传文件
		 *
		 */
		public function uploadAction()
		{
			$fileElementName	= 'uploadexp';
			if (!isset($_FILES[$fileElementName]))
			{
				return $this->err(1010, LANG::E_UPLOAD_NO_FILE);
			}
			if(!empty($_FILES[$fileElementName]['error']))
			{
				switch($_FILES[$fileElementName]['error'])
				{
					case '1':
						$errcode	= 1011;
						$errinfo	= LANG::E_UPLOAD_ERR_INI_SIZE;
						break;
					case '2':
						$errcode	= 1012;
						$errinfo	= LANG::E_UPLOAD_ERR_FORM_SIZE;
						break;
					case '3':
						$errcode	= 1013;
						$errinfo	= LANG::E_UPLOAD_ERR_PARTIAL;
						break;
					case '4':
						$errcode	= 1014;
						$errinfo	= LANG::E_UPLOAD_ERR_NO_FILE;
						break;
					case '6':
						$errcode	= 1016;
						$errinfo	= LANG::E_UPLOAD_ERR_NO_TMP_DIR;
						break;
					case '7':
						$errcode	= 1017;
						$errinfo	= LANG::E_UPLOAD_ERR_CANT_WRITE;
						break;
					case '8':
						$errcode	= 1018;
						$errinfo	= LANG::E_UPLOAD_NO_AVAIABLE;
						break;
					default:
						$errcode	= 1019;
						$errinfo	= LANG::E_UPLOAD_STOP_BY_EXTEN;
				}
				return $this->err($errcode, $errinfo);
			}
			elseif (empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none')
			{
				return $this->err(1010, LANG::E_UPLOAD_NO_FILE);
			}
			// ** 文件是否存在
			if (is_file($this->realdir.$_FILES[$fileElementName]['name']))
			{
				return $this->err(1009, LANG::E_UPLOAD_FILE_EXISTS);
			}
			// ** 保存上传文件
			if (!move_uploaded_file($_FILES[$fileElementName]['tmp_name'], $this->realdir.$_FILES[$fileElementName]['name']))
			{
				return $this->err(1020, LANG::E_UPLOAD_SAVE_FAILURE);
			}
			$name	= $_FILES[$fileElementName]['name'];
			$this->x('name', $name);
			$this->x('path', $this->currdir.$name);
			$this->x('url', UPLOAD_URL.$this->currdir.$name);
			$this->x('icon', $this->_file_ext($name));
		}
		
		/**
		 * 返回文件的大小与md5值
		 *
		 */
		public function queryAction()
		{
			$file	= $this->io->r('file');
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
		}
		
		
		public function _file_ext($name)
		{
			return '-';
		}
		
		
	}
	
?>