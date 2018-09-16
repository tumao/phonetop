<?php

	class Apktool extends ZQFramework\Core\Singleton 
	{
		private $_basedir	= '';
		private $_curr_file	= '';
		
		const EXTRA_CACHE	= '/tmp/.apk_cache/';
		const APKTOOL_JAR	= 'apktool.jar';
		const COMMAND_FMT	= '%saapt d badging %s';
	
	
		protected function __construct()
		{
			if (!is_dir(self::EXTRA_CACHE))
			{
				mkdir(self::EXTRA_CACHE);
			}
			$this->_basedir	= __DIR__._;
		}
				
		public function getApkInfo($apkfile)
		{
			$this->_curr_file	= '';
			if (!is_file($apkfile))
			{
				return false;
			}
			$this->_curr_file	= $apkfile;
			$name		= basename($apkfile);
			$temp		= self::EXTRA_CACHE.$name;
			$command	= sprintf(self::COMMAND_FMT, $this->_basedir, $apkfile);
			$result		= null;
			$retcode	= -1;
			@exec($command, $result, $retcode);
			if ($retcode !== 0 || empty($result))
			{
				return false;
			}
			$configure	= array();
			foreach ($result as $line)
			{
				$line	= trim($line);
				if (empty($line))
				{
					continue;
				}
				$config	= explode(':', $line);
				if (count($config) < 2)
				{
					continue;
				}
				$key	= $config[0];
				$data	= $config[1];
				$configure[$key]	= $this->parseKeyValue($data);
			}
			return $configure;
		}
		
		public function getFileFrom($file)
		{
			if (empty($this->_curr_file) || empty($file))
			{
				return false;
			}
			$z	= new ZipArchive();
			$z->open($this->_curr_file);
			$data = $z->getFromName($file);
			$z->close();
			$logodir	= UPLOAD_BASE_DIR._;
			$uripath	= _.'apk-icon'._;
			$logodir	= UPLOAD_BASE_DIR.$uripath;
			if (!is_dir($logodir))
			{
				mkdir($logodir);
			}
			$uripath .= date('Y-m-d')._;
			$logodir  = UPLOAD_BASE_DIR.$uripath;
			if (!is_dir($logodir))
			{
				mkdir($logodir);
			}
			$name	= substr(time(), -5) . '-';
			$name  .= basename($file);
			$logo	= $logodir.$name;
			$uripath .= $name;
			file_put_contents($logo, $data);
			return array('url' => UPLOAD_URL.$uripath, 'path' => $uripath);
		}
		
		private function parseKeyValue($data)
		{
			$data	= trim($data);
			if (empty($data))
			{
				return '';
			}
			$regex	= "/([a-z0-9]+)=\'([^\']*)\'/i";
			$conf	= array();
			$match	= array();
			preg_match_all($regex, $data, $match);
			if (empty($match[0]))
			{
				$clean	= substr($data, 1, -1);
				if (strpos($clean, "','"))
				{
					$clean	= explode("','", $clean);
				}
				return $clean;
			}
			foreach ($match[1] as $idx => $key)
			{
				$conf[$key]	= $match[2][$idx];
			}
			return $conf;
		}
		
		
	}

?>