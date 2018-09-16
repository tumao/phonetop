<?php

	class UserController extends BaseController
	{
		protected function init()
		{
		}
		
		protected function free()
		{
			
		}
		
		public function indexAction()
		{
			
		}
		
		public function loginAction()
		{
			if ($this->env->session('UserID'))
			{
				header('location: /');
			}
			$isSucc	= false;
			/* 用户实例 */
			$user	= DB::ORM('Phone::User');
			$userid	= 0;
			$username	= NULL;
			$hashkey	= NULL;
			$remember	= 'OFF';
			$redirect	= false;
			if ($this->env->isp())
			{
				/* 登录帐号 */
				$username	= $this->io->p('username');
				$username	= strtolower(trim($username));
				/* 登录密码 */
				$password	= $this->io->p('password');
				$password	= trim($password);
				/* 是否记住密码 */
				$remember	= $this->io->p('remember');
				$user->username	= $username;
				$user->load();
				$userid	= $user->id;
				if (empty($userid))
				{
					return $this->err(101, LANG::E_USER_LOGIN_USERNAME);
				}
				else if ($user->password != md5($password))
				{
					return $this->err(102, LANG::E_USER_LOGIN_PASSWORD);
				}
				else if (!($user->stat & 0x01))
				{
					return $this->err(102, LANG::E_USER_LOGIN_DENY);
				}
				$isSucc	= true;
			}
			else
			{
				$user_salt	= NULL;
				$hash_salt	= NULL;
				foreach ($_COOKIE as $name => $value)
				{
					$conf	= explode('_', $name);
					if (!isset($conf[1]) || empty($conf[1]))
					{
						continue;
					}
					if ($conf[0] == 'uid')
					{
						$userid		= $value;
						$user_salt	= $conf[1];
					}
					else if ($conf[0] == 'hash')
					{
						$hashkey	= $value;
						$hash_salt	= $conf[1];
					}
				}
				if (!empty($userid))
				{
					$user->id	= $userid;
					$user->load();
					$username	= $user->username;
					$hash		= NULL;
					if (!empty($username) && ($user->stat & 0x01))
					{
						$hexuid	= sprintf("%08X", $userid);
						$hash	= md5(md5($username.$user_salt).$hexuid.$hash_salt);
						if ($hash == $hashkey) $isSucc = true;
					}
					$remember	= 'ON';
				}
				$redirect	= true;
			}
			if ($isSucc)
			{
				/* 记住密码 */
				if ($remember == 'ON')
				{
					$expireIn	= time() + 604800;
					// var_dump($_SERVER['HTTP_USER_AGENT']);
					$uid	= $user->id;
					$this->env->setcookie('uid', $uid, $expireIn);
					// ** cookie-hash 串生成规则
					// md5(md5(${USERNAME} + ${USER_SALT}) + sprintf("%08X", ${UID}) + ${HASH_SALT})
					// salt 值通过 参数名 UID 与 HASH 取出。
					$user_salt	= $this->env->salt('uid');
					$hash_salt	= $this->env->salt('hash');
					$hexuid	= sprintf("%08X", $userid);
					$hash	= md5(md5($username.$user_salt).$hexuid.$hash_salt);
					$this->env->setcookie('hash', $hash, $expireIn);
				}
				$this->env->session('UserID', $userid);
				$this->env->session('username', $user->username);
				$this->env->session('realname', $user->realname);
				if ($redirect) header('Location: /');
				return true;
			}
			// ** 页面的CSS及JS
			$this->addcss('user-login.css');
			$this->addscript('app/user-login.js');
			// ** 清除对应salt的cookie值
			$this->env->clearExpireCookie();
		}
		
		public function logoutAction()
		{
			unset($_SESSION['UserID']);
			unset($_SESSION['username']);
			unset($_SESSION['realname']);
			$this->env->clearCookie();
			header('Location: /');
		}
	
		public function passwdAction()
		{
			if ($this->env->isp())
			{
				$uid	= $this->env->session('UserID');
				$user	= DB::ORM('Phone::User');
				$user->id	= $uid;
				$user->load();
				$userid	= $user->id;
				$passwd	= $user->password;
				$oldpwd	= $this->io->r('old_password', INPUT_SQL);
				if (empty($userid) || $passwd!=md5($oldpwd))
				{
					return $this->err(103, LANG::E_USER_NAME_PASSWORD);
				}
				$newpassword	= $this->io->r('new_password', INPUT_SQL);
				$repassword		= $this->io->r('re_password', INPUT_SQL);
				if ($newpassword != $repassword)
				{
					return $this->err(104, LANG::E_USER_CHECK_NEW_PASSWORD);
				}
				$user->password	= md5($newpassword);
				if (!$user->update())
				{
					return $this->err(105, LANG::E_USER_CHANGE_PASSWORD);
				}
			}
			$this->addscript('app/user-password.js');
		}
		
		public function manageAction()
		{
			$uid	= $this->env->session('UserID');
			$user	= DB::ORM('Phone::User');
			$user->id	= $uid;
			$user->load();
			$stat	= $user->stat;
			$permit	= false;
			if ($stat & 0x80)
			{
				$permit	= true;
			}
			if ($this->env->isp())
			{
				if (!$permit)
				{
					return $this->err(106, LANG::E_USER_PERMIT_DENY);
				}
				$command	= $this->io->r('command');
				return $this->doCommand($command);
			}
			$this->addscript('app/user-manage.js');
		}
		
		private function doCommand($command)
		{
			$commandmethod	= 'Command_user_'.strtolower($command);
			if (!method_exists($this, $commandmethod))
			{
				return $this->err(100, LANG::E_ACCESS_DENY);
			}
			return $this->$commandmethod();
		}
		
		private function Command_user_list()
		{
			// ** easyui-pagination 中传过来的参数
			$rows	= $this->io->p('rows');
			$page	= $this->io->p('page');
			// ** 处理参数，防止非数字参数值
			$page	= intval($page);
			$page	= $page < 1 ? 1 : $page;
			$rows	= intval($rows);
			$rows	= $rows < 10 ? 10 : $rows;
			$offset	= ($page - 1) * $rows;
			// ** 对象
			$user	= DB::ORM('Phone::User');
			$query	= array();
			$order	= "`username` ASC";
			$total	= $user->countBy($query);
			$list	= $user->listBy($query, $rows, $offset, $order);
			$this->x('total', $total);
			$result	= array();
			foreach ($list as $row)
			{
				$result[]	= array(
					'id'		=> $row['id'],
					'username'	=> $row['username'],
					'realname'	=> $row['realname'],
					'stat'		=> $row['stat'].':'.$row['id'],
					'time'		=> $row['time']
				);
			}
			$this->x('rows', $result);
		}
		
		private function Command_user_info()
		{
			$id		= $this->io->r('id');
			$user	= DB::ORM('Phone::User');
			$info	= $user->infoBy(array('id' => $id));
			$this->io->view_assign('info', $info);
			$formdata	= $this->io->view_fetch('user/manage-form.tpl');
			$this->x('formdata', $formdata);
		}
		
		private function Command_user_save()
		{
			$id		= $this->io->r('id');
			$user	= DB::ORM('Phone::User');
			$user->id	= $id;
			$user->load();
			$userid		= $user->id;
			$username	= $this->io->r('username');
			$realname	= $this->io->r('realname');
			$passowrd	= $this->io->r('password');
			$issuper	= $this->io->r('issuper');
			$issuper	= $issuper == 'ON' ? true : false;
			$realname	= empty($realname) ? $username : $realname;
			$savemethod	= 'update';
			if (empty($userid))
			{
				if (!empty($id))
				{
					return $this->err(107, LANG::E_MANAG_USERE_NOT_FOUND);
				}
				$passowrd		= empty($passowrd) ? substr(md5($username.time()), 0, rand(6, 10)) : $passowrd;
				$user->password	= md5($passowrd);
				$user->stat		= $issuper ? 0x81 : 0x01;
				$savemethod		= 'insert';
			}
			$user->username	= $username;
			$user->realname	= $realname;
			$user->idcard	= '-';
			if (!$user->$savemethod())
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
		}
		
		private function Command_user_chg()
		{
			$id		= $this->io->r('id');
			$user	= DB::ORM('Phone::User');
			$user->id	= $id;
			$user->load();
			$userid	= $user->id;
			if (empty($userid))
			{
				return $this->err(107, LANG::E_MANAG_USERE_NOT_FOUND);
			}
			$flag	= $this->io->p('flag');
			$icon	= 'ok';
			$stat	= $user->stat;
			if ($stat & 0x01)
			{
				$icon	= 'no';
				$user->stat	= $stat & 0xFE;
			}
			else
			{
				$user->stat	= $stat | 0x01;
			}
			if (!$user->update())
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
			return $this->x('icon', $icon);
		}
		
		private function Command_user_del()
		{
			$id		= $this->io->r('id');
			$user	= DB::ORM('Phone::User');
			$user->id	= $id;
			$user->load();
			$userid	= $user->id;
			if (empty($userid))
			{
				return $this->err(107, LANG::E_MANAG_USERE_NOT_FOUND);
			}
			if (!$user->delete())
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
		}
		
		private function Command_user_passwdreset()
		{
			$id		= $this->io->r('id');
			$user	= DB::ORM('Phone::User');
			$user->id	= $id;
			$user->load();
			$userid	= $user->id;
			if (empty($userid))
			{
				return $this->err(107, LANG::E_MANAG_USERE_NOT_FOUND);
			}
			$password	= $this->io->r('password');
			$user->password	= md5($password);
			if (!$user->update())
			{
				return $this->err(1001, LANG::E_DB_OPERATION);
			}
		}
		
	}

?>