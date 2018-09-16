<?php
	/**
	 * DB
	 *
	 * @package Framework\Core
	 *
	 * @author 李文强 <liwenqiang@shoujids.com>
	 * @copyright 朱雀网络
	 * @version 1.0
	 */
	namespace ZQFramework\Core
	{


		class DBProvider
		{

			/**
			 * 实例对象
			 *
			 * 不能使用Singleton类来实现，
			 * 否则在继承后使用，在ORM类中又生成新的实例
			 */
			private static $mInstance	= NULL;

			/**
			 * 数据库相关配置
			 */
			private $configure	= array();

			/**
			 * 数据库对应的连接实例对象
			 */
			private $connections	= array();

			/**
			 * 对应DB中存在的表
			 *
			 * [DB][_m_table][<fieldname>]
			 */
			private $_db_tables		= array();

			/**
			 * 已经通过 dirkey 验证的 OrmReflection
			 */
			private $_orm_confirmed	= array();
			/**
			 * 对应了解析出来的 linker, DB与TABLE值
			 * DB 值为抽象的库名，为ORM文件夹下目录名，
			 * TABLE 值为ORM类名前缀，非真正的表名
			 * _m_table 为真正数据库中对应的表名
			 * _m_prkey 为表对应的主键名数组，若对应字段为自增值为AC,否则为PRI
			 * 真正的数据库名可以通过linker从$configure中取得。
			 * [$target] => {linker: '', DB: '', TABLE: '', _m_table: ''}
			 */
			private $_orm_configure	= array();
			/**
			 * 对应dirkey的查找表
			 *
			 * 通过表查找对应 $_orm_confirmed 中的键
			 */
			private $_map_confirmed	= array();

			/**
			 * 最后一次执行的SQL语句
			 */
			private $_last_exec_sql	= '';

			/**
			 * Load函数中，$dirkey使用的分隔符
			 */
			const DIRKEY_SEPARATOR	= '::';

			/**
			 * ORM文件类名后缀
			 */
			const ORM_FILE_SUFFIX	= '.orm.php';
			/**
			 * ORM类名后缀
			 */
			const ORM_CLASS_SUFFIX	= 'ORM';

			/**
			 * ORM类中，对应连接的值
			 */
			const ORM_CONFIRMED_TARGET	= '__ORM_TARGET';

			/**
			 * 对应数据库操作类的单例实现
			 *
			 * 不能使用Singleton类实现，否则继承的子类与父类拥有不同的实例对象。
			 */
			public static function & getInstance()
			{
				if (self::$mInstance == NULL)
				{
					$called	= get_called_class();
					self::$mInstance = new $called;
				}
				return self::$mInstance;
			}

			/**
			 * 初始化数据操作类
			 *
			 * 防止类似UserORM等同名的ORM类重复定义，
			 * ORM类需要带上 \ORM\<DB> 命名空间。
			 *
			 * $dirkey参数格式： DB::LINKER::TABLE
			 * ***
			 * DB 为对应抽象的库名，一个 DB 可对应多个LINKER
			 * 因为 DB 可以在不同主机上也可以有不同的库名
			 * ***
			 * LINKER 为数据库链接标识，LINKER对应到具体的库名
			 * ***
			 * TABLE 为对应表的ORM抽象名
			 * ***
			 * DB值可以为空，DB值为空时取LINKER中的数据库名
			 * ORM文件存放，在ORM目录下，DB/TABLE.orm.php 文件
			 * ORM文件继承自 DBORM类，需要定义 $_m_table 值，对应数据库中的表名
			 *
			 * @param string $dirkey	数据ORM类路径定义
			 * @return DBORM
			 */
			public static function ORM($dirkey)
			{
				$self	= self::getInstance();
				$mkey	= $self->getOrmReflectionFrom($dirkey);
				$ormInstance	= call_user_func_array(array(
					$self->_orm_confirmed[$mkey],
					'newInstance'
				), array());
				$ormkey	= self::ORM_CONFIRMED_TARGET;
				$ormInstance->$ormkey	= $mkey;
				return $ormInstance;
			}

			/**
			 * 连接对象
			 *
			 */
			public function &getConnection($target)
			{
				return $this->connections[$this->_orm_configure[$target]['linker']];
			}

			/**
			 * 通过主键相关值读入记录
			 *
			 * 只查询主键相关的记录，主键的记录只会有一条
			 * 若查询的是非主键的单条记录，使用 loadone() 方法
			 */
			public function load($target, $data, $update=NULL)
			{
				if (!is_array($data) || empty($data))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_INSERT_PARAM_DATA_ERROR);
				}
				$where	= array();
				foreach ($data as $key => $value)
				{
					if (isset($this->_db_tables[$this->_orm_configure[$target]['DB']][$this->_orm_configure[$target]['_m_table']][$key]))
					{
						$where[]	= "`{$key}`='{$value}'";
					}
				}
				if (empty($where))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_QUERY_CONDITION_IS_EMPTY);
				}
				$sql  = "SELECT * FROM `{$this->_orm_configure[$target]['_m_table']}` WHERE " . implode(' AND ', $where);
				$this->_last_exec_sql	= $sql;
				$result	= $this->connections[$this->_orm_configure[$target]['linker']]->query($sql);
				/* orm_row_data 中返回记录值 */
				return array(
					'orm_row_flag'	=> true,
					'orm_row_data'	=> $result->fetch()
				);
			}

			/**
			 * 查询返回单条记录
			 *
			 *
			 */
			public function loadone($target, $data, $update=NULL)
			{
				if (!is_array($data) || empty($data))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_INSERT_PARAM_DATA_ERROR);
				}
				$where	= array();
				foreach ($data as $key => $value)
				{
					$where[]	= "`{$key}`='{$value}'";
				}
				if (empty($where))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_QUERY_CONDITION_IS_EMPTY);
				}
				$sql  = "SELECT * FROM `{$this->_orm_configure[$target]['_m_table']}` WHERE " . implode(' AND ', $where);
				$this->_last_exec_sql	= $sql;
				$result	= $this->connections[$this->_orm_configure[$target]['linker']]->query($sql);
				/* orm_row_data 中返回记录值 */
				return array(
					'orm_row_flag'	=> true,
					'orm_row_data'	=> $result->fetch()
				);
			}

			/**
			 * ORM单记录操作：新增记录
			 */
			public function insert($target, $data, $update=NULL)
			{
				if (!is_array($data) || empty($data))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_INSERT_PARAM_DATA_ERROR);
				}
				$fields	= array();
				$insert	= array();
				$prkey	= NULL;
				foreach ($data as $key => $value)
				{
					if (isset($this->_db_tables[$this->_orm_configure[$target]['DB']][$this->_orm_configure[$target]['_m_table']][$key]))
					{
						if ($this->_db_tables[$this->_orm_configure[$target]['DB']][$this->_orm_configure[$target]['_m_table']][$key]['key'] == 'PRI' && strpos($this->_db_tables[$this->_orm_configure[$target]['DB']][$this->_orm_configure[$target]['_m_table']][$key]['extra'], 'auto_increment') !== false)
						{
							continue;
						}
						$fields[]	= $key;
						$insert[]	= $value;
					}
				}
				if (empty($fields))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_INSERT_FIELD_IS_EMPTY);
				}
				foreach ($this->_orm_configure[$target]['_m_prkey'] as $key => $val)
				{
					if ($val == 'AC')
					{
						$prkey	= $key;
					}
				}
				$sql  = "INSERT INTO `{$this->_orm_configure[$target]['_m_table']}` (`";
				$sql .= implode('`, `', $fields);
				$sql .= "`) VALUE ('";
				$sql .= implode("', '", $insert);
				$sql .= "')";
				$this->_last_exec_sql	= $sql;
				$this->connections[$this->_orm_configure[$target]['linker']]->exec($sql);
				/* orm_row_data 中只带新增的主键ID值 */
				return array(
					'orm_row_flag'	=> true,
					'orm_row_data'	=> array(
						$prkey		=> $this->connections[$this->_orm_configure[$target]['linker']]->lastInsertId()
					)
				);
			}

			/**
			 * ORM单记录操作：更新记录
			 */
			public function update($target, $data, $update=NULL)
			{
				if (!is_array($data) || empty($data))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_INSERT_PARAM_DATA_ERROR);
				}
				$array	= array();
				$where	= array();
				if (empty($update) || !is_array($update))
				{
					return true;
				}
				foreach ($update as $key => $value)
				{
					if (isset($this->_db_tables[$this->_orm_configure[$target]['DB']][$this->_orm_configure[$target]['_m_table']][$key]))
					{
						$array[]	= "`{$key}`='{$value}'";
					}
				}
				foreach ($data as $key => $value)
				{
					if (isset($this->_db_tables[$this->_orm_configure[$target]['DB']][$this->_orm_configure[$target]['_m_table']][$key]))
					{
						if ($this->_db_tables[$this->_orm_configure[$target]['DB']][$this->_orm_configure[$target]['_m_table']][$key]['key'] == 'PRI')
						{
							$where[]	= "`{$key}`='{$value}'";
						}
					}
				}
				if (empty($where))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_QUERY_CONDITION_IS_EMPTY);
				}
				$sql  = "UPDATE `{$this->_orm_configure[$target]['_m_table']}` SET ";
				$sql .= implode(', ', $array);
				$sql .= " WHERE ".implode(' AND ', $where);
				$this->_last_exec_sql	= $sql;
				$this->connections[$this->_orm_configure[$target]['linker']]->exec($sql);
				/* orm_row_data 返回 true 时，更新 __m_data_update 中数据到 __m_data_storage 中，更新操作中返回 true */
				/* orm_row_data 返回 false 时，清空 __m_data_update 和 __m_data_storage 数组，删除操作时返回 false */
				return array(
					'orm_row_flag'	=> true,
					'orm_row_data'	=> true
				);
			}

			/**
			 * ORM单记录操作：删除记录
			 */
			public function delete($target, $data, $update=NULL)
			{
				if (!is_array($data) || empty($data))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_INSERT_PARAM_DATA_ERROR);
				}
				$where	= array();
				foreach ($data as $key => $value)
				{
					if (isset($this->_db_tables[$this->_orm_configure[$target]['DB']][$this->_orm_configure[$target]['_m_table']][$key]))
					{
						if ($this->_db_tables[$this->_orm_configure[$target]['DB']][$this->_orm_configure[$target]['_m_table']][$key]['key'] == 'PRI')
						{
							$where[]	= "`{$key}`='{$value}'";
						}
					}
				}
				$sql  = "DELETE FROM `{$this->_orm_configure[$target]['_m_table']}` WHERE ";
				$sql .= implode(' AND ', $where);
				$this->_last_exec_sql	= $sql;
				$this->connections[$this->_orm_configure[$target]['linker']]->exec($sql);
				/* orm_row_data 返回 true 时，更新 __m_data_update 中数据到 __m_data_storage 中，更新操作中返回 true */
				/* orm_row_data 返回 false 时，清空 __m_data_update 和 __m_data_storage 数组，删除操作时返回 false */
				return array(
					'orm_row_flag'	=> true,
					'orm_row_data'	=> false
				);
			}



			public function listBy($target, $hash=NULL, $limit=0, $offset=0, $order=NULL)
			{
				$where	= NULL;
				if (!empty($hash) && is_array($hash))
				{
					$where	= $this->genQueryFor($target, $hash);
				}
				$sql	= "SELECT * FROM `{$this->_orm_configure[$target]['_m_table']}` ";
				if (!empty($where))
				{
					$sql .= " WHERE {$where}";
				}
				if (!empty($order))
				{
					$sql .= " ORDER BY {$order} ";
				}
				$limit	= intval($limit);
				if (!empty($limit))
				{
					$sql   .= " LIMIT ";
					$offset	= intval($offset);
					if (!empty($offset))
					{
						$sql .= " {$offset}, ";
					}
					$sql  .= $limit;
				}
				return $this->listByQuery($target, $sql);
			}

			public function selectionListBy($target, $selection, $hash=NULL, $limit=0, $offset=0, $order=NULL)
			{
				$where	= NULL;
				if (!empty($hash) && is_array($hash))
				{
					$where	= $this->genQueryFor($target, $hash);
				}
				$sql	= "SELECT *, {$selection} FROM `{$this->_orm_configure[$target]['_m_table']}` ";
				if (!empty($where))
				{
					$sql .= " WHERE {$where}";
				}
				if (!empty($order))
				{
					$sql .= " ORDER BY {$order} ";
				}
				$limit	= intval($limit);
				if (!empty($limit))
				{
					$sql   .= " LIMIT ";
					$offset	= intval($offset);
					if (!empty($offset))
					{
						$sql .= " {$offset}, ";
					}
					$sql  .= $limit;
				}
				return $this->listByQuery($target, $sql);
			}

			public function countBy($target, $hash=NULL)
			{
				$where	= NULL;
				if (!empty($hash) && is_array($hash))
				{
					$where	= $this->genQueryFor($target, $hash);
				}
				$sql	= "SELECT count(*) FROM `{$this->_orm_configure[$target]['_m_table']}` ";
				if (!empty($where))
				{
					$sql .= " WHERE {$where}";
				}
				Log::getInstance()->fire($sql);
				$result	= $this->connections[$this->_orm_configure[$target]['linker']]->query($sql);
				$data	= $result->fetch();
				$count	= array_pop($data);
				return $count;
			}

			public function countByQuery($target, $where)
			{
				$sql	= "SELECT count(*) FROM `{$this->_orm_configure[$target]['_m_table']}` WHERE {$where}";
				Log::getInstance()->fire($sql);
				$result	= $this->connections[$this->_orm_configure[$target]['linker']]->query($sql);
				$data	= $result->fetch();
				$count	= array_pop($data);
				return $count;
			}

			public function infoBy($target, $hash=NULL)
			{
				$where	= NULL;
				if (!empty($hash) && is_array($hash))
				{
					$where	= $this->genQueryFor($target, $hash);
				}
				$sql	= "SELECT * FROM `{$this->_orm_configure[$target]['_m_table']}` ";
				if (!empty($where))
				{
					$sql .= " WHERE {$where}";
				}
				$sql .= " LIMIT 1";
				Log::getInstance()->fire($sql);
				$result	= $this->connections[$this->_orm_configure[$target]['linker']]->query($sql);
				$data	= $result->fetch();
				if (!$data)
				{
					foreach ($this->_db_tables[$this->_orm_configure[$target]['DB']][$this->_orm_configure[$target]['_m_table']] as $field => $val)
					{
						$data[$field]	= '';
					}
				}
				return $data;
			}


			/**
			 * 通过主键ID的键指删除
			 *
			 */
			public function deleteForKey($target, $key, $values)
			{
				if (!isset($this->_db_tables[$this->_orm_configure[$target]['DB']][$this->_orm_configure[$target]['_m_table']][$key]))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_ORM_FIELD_NOT_FOUND);
				}
				if(!is_array($values) || empty($values))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_ORM_PARAM_MUST_BE_ARRAY);
				}
				$where	= "`{$key}` IN ('" . implode("', '", $values) . "')";
				$sql	= "DELETE FROM `{$this->_orm_configure[$target]['_m_table']}` WHERE {$where}";
				return $this->executeQuery($target, $sql);
			}


			public function listByQuery($target, $sql)
			{
				$this->_last_exec_sql	= $sql;
				Log::getInstance()->fire($sql);
				$result	= $this->connections[$this->_orm_configure[$target]['linker']]->query($sql);
				$list	= array();
				while ($row = $result->fetch())
				{
					$list[]	= $row;
				}
				return $list;
			}

			public function executeQuery($target, $sql)
			{
				$this->_last_exec_sql	= $sql;
				return $this->connections[$this->_orm_configure[$target]['linker']]->exec($sql);
			}



			public function genQueryFor($target, $hash)
			{
				if (!is_array($hash))
				{
					return $hash;
				}
				$where	= array();
				foreach ($hash as $key => $val)
				{
					if (isset($this->_db_tables[$this->_orm_configure[$target]['DB']][$this->_orm_configure[$target]['_m_table']][$key]))
					{
						$where[]	= "`{$key}`='{$val}'";
					}
				}
				if (isset($hash['_']))
				{
					if (!is_array($hash['_']))
					{
						$where[]	= $hash['_'];
					}
					else
					{
						foreach ($hash['_'] as $query)
						{
							$where[]	= $query;
						}
					}
				}
				$condition	= implode(' AND ', $where);
				return $condition;
			}


			/**
			 * DBAdapter构造函数
			 *
			 * 检查DB的相关配置，及加载数据库相关配置文件
			 */
			private function __construct()
			{
				if (!defined('ORM'))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_ORM_DIRECTORY_IS_NOT_DEFINED);
				}
				if (!is_dir(ORM))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_ORM_DIRECTORY_IS_NOT_FOUND);
				}
				if (!defined('DB_CONFIG'))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_CONFIG_IS_NOT_DEFINED);
				}
				$configure_file	= str_replace('{ENV}', __ENV_GLOBAL, DB_CONFIG);
				if (!is_file($configure_file))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_CONFIG_IS_NOT_FOUND);
				}
				$configure	= require($configure_file);
				if (!empty($configure) && is_array($configure))
				{
					foreach ($configure as $key => $conf)
					{
						if (!is_array($conf))
						{
							continue;
						}
						if (!isset($conf['user']) || empty($conf['user']))
						{
							throw new ZQException(\ZQFramework\Lang\LANG::DB_CONFIG_USER_IS_EMPTY);
						}
						if (!isset($conf['dbname']) || empty($conf['dbname']))
						{
							throw new ZQException(\ZQFramework\Lang\LANG::DB_CONFIG_DBNAME_IS_EMPTY);
						}
						$this->configure[$key]['user']	= $conf['user'];
						$this->configure[$key]['host']	= isset($conf['host']) ? $conf['host'] : 'localhost';
						$this->configure[$key]['port']	= isset($conf['port']) ? $conf['port'] : 3306;
						$this->configure[$key]['dbname']	= $conf['dbname'];
						$this->configure[$key]['password']	= isset($conf['password']) ? $conf['password'] : '';
						$this->configure[$key]['options']	= (isset($conf['options']) && is_array($conf['options'])) ? $conf['options'] : false;
					}
				}
			}

			/**
			 * 验证对应的key值是否表的字段
			 *
			 * @param string $target
			 * @param string $field
			 */
			public function isForORM($target, $field)
			{
				return isset($this->_db_tables
					[ $this->_orm_configure[$target]['DB'] ]			// dbname
					[ $this->_orm_configure[$target]['_m_table'] ]		// tablename
					[ $field ]											// fieldname
				);
			}

			public function & getConnectionFor($target)
			{
				return $this->connections[$this->_orm_configure[$target]['linker']];
			}

			/**
			 * 通过dirkey值查找对应的OrmReflection
			 *
			 * 若对应的dirkey已验证，直接返回OrmReflection对象，
			 * 若未经验证，则对该值进行验证。
			 */
			private function getOrmReflectionFrom($dirkey)
			{
				$dirkey	= str_replace(' ', '', $dirkey);
				if (empty($dirkey))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_LOAD_DIRKEY_IS_EMPTY);
				}
				$loaded	= explode(self::DIRKEY_SEPARATOR, $dirkey);
				$config	= array();
				foreach ($loaded as $key)
				{
					$key	= trim($key);
					if (!empty($key))
					{
						$config[]	= $key;
					}
				}
				$size	= count($config);
				if ($size < 2 || $size > 3)
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_LOAD_DIRKEY_SIZE_ERROR);
				}
				$table	= array_pop($config);
				$linker	= array_pop($config);
				if (!isset($this->configure[$linker]))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_LOAD_DIRKEY_LINK_NOT_FOUND);
				}
				$dbname	= empty($config) ? $this->configure[$linker]['dbname'] : array_pop($config);
				$_full_taget	= $dbname . self::DIRKEY_SEPARATOR . $linker . self::DIRKEY_SEPARATOR . $table;
				if (isset($this->_map_confirmed[$dbname][$_full_taget]))
				{
					return $this->_map_confirmed[$dbname][$_full_taget];
				}
				if (!is_dir(ORM.$dbname))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_LOAD_DIRECTORY_NOT_FOUND);
				}
				$filename	= ORM.$dbname._.$table.self::ORM_FILE_SUFFIX;
				if (!is_file($filename))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_LOAD_FILE_NOT_FOUND);
				}
				require($filename);
				$namespace	= 'ORM\\'.$dbname.'\\';
				$called		= $table.self::ORM_CLASS_SUFFIX;
				if (!class_exists($namespace.$called))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_LOAD_CLASS_NOT_FOUND);
				}
				$reflection	= new \ReflectionClass($namespace.$called);
				$parent		= $reflection->getParentClass();
				$extends	= NULL;
				while ($parent)
				{
					$extends	= $parent->getName();
					$parent		= $parent->getParentClass();
				}
				if (empty($extends) || $extends !=  'ZQFramework\Core\DBORM')
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_LOAD_CLASS_NOT_EXTENDS_DBORM);
				}
				$tablename	= $reflection->getConstant('TABLE');
				if (empty($tablename))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_ORM_CONSTANT_TABLE_NOT_DEFINED);
				}
				$tablename	= strtolower($tablename);
				$this->doConnectForLinker($linker, $dbname);
				if (!isset($this->_db_tables[$dbname][$tablename]))
				{
					throw new ZQException(\ZQFramework\Lang\LANG::DB_ORM_CONSTANT_TABLE_NOT_FOUND);
				}
				$prkey	= $this->doDescriptForTable($linker, $dbname, $tablename);
				$this->_map_confirmed[$dbname][$_full_taget]	= $_full_taget;
				if ($_full_taget == $this->configure[$linker]['dbname'])
				{
					$this->_map_confirmed[$dbname][$linker . self::DIRKEY_SEPARATOR . $table]	= $_full_taget;
				}
				if (!isset($this->_map_confirmed[$dbname][$dirkey]))
				{
					$this->_map_confirmed[$dbname][$dirkey]	= $_full_taget;
				}
				$this->_orm_confirmed[$_full_taget]	= & $reflection;
				$this->_orm_configure[$_full_taget]	= array(
					'linker'	=> $linker,
					'TABLE'		=> $table,
					'DB'		=> $dbname,
					'_m_table'	=> $tablename,
					'_m_prkey'	=> $prkey
				);
				return $_full_taget;
			}

			/**
			 * MySQL连接
			 */
			private function doConnectForLinker($linker, $dbname)
			{
				if (isset($this->connections[$linker]))
				{
					return true;
				}
				$dsn  = 'mysql:';
				$dsn .= 'host='.$this->configure[$linker]['host'].';';
				$dsn .= 'port='.$this->configure[$linker]['port'].';';
				$dsn .= 'dbname='.$this->configure[$linker]['dbname'];
				$options	= array(
					\PDO::ATTR_CASE		=> \PDO::CASE_LOWER,
					\PDO::ATTR_ERRMODE	=> \PDO::ERRMODE_EXCEPTION,
					\PDO::ATTR_DEFAULT_FETCH_MODE	=> \PDO::FETCH_ASSOC
				);
				if (!empty($this->configure[$linker]['options']))
				{
					foreach ($this->configure[$linker]['options'] as $attr => $setting)
					{
						$options[$attr]	=	$setting;
					}
				}
				$this->connections[$linker]	= new \PDO(
					$dsn,
					$this->configure[$linker]['user'],
					$this->configure[$linker]['password'],
					$options
				);
				$this->connections[$linker]->exec("SET NAMES UTF8");
				$statment	= $this->connections[$linker]->prepare("SHOW TABLES");
				$statment->execute();
				while ($row = $statment->fetch(\PDO::FETCH_NUM))
				{
					$this->_db_tables[$dbname][strtolower($row[0])]	= array();
				}
			}

			/**
			 * 解析表中字段
			 */
			private function doDescriptForTable($linker, $dbname, $tablename)
			{
				$statment	= $this->connections[$linker]->prepare("DESC `{$tablename}`");
				$statment->execute();
				$prkey		= array();
				while ($row = $statment->fetch())
				{
					if ($row['extra'] == 'auto_increment')
					{
						$prkey[$row['field']]	= 'AC';
					}
					else if ($row['key'] == 'PRI')
					{
						$prkey[$row['field']]	= 'PRI';
					}
					$this->_db_tables[$dbname][$tablename][$row['field']]	= $row;
				}
				return $prkey;
			}

		}//~: __END_OF_CLASS__________


		abstract class DBORM
		{
			/**
			 * 对应表字段中的值
			 */
			private $__m_data_storage	= array();

			/**
			 * 需要更新的字段
			 */
			private $__m_data_update	= array();

			/**
			 * 是否对应数据库中的数据
			 *
			 * 针对单记录操作完成后标志位更新为true
			 * 为true时，执行setter方法时更改__m_data_update数组
			 */
			private $__m_data_loaded	= false;

			public function __construct()
			{

			}

			public function __destruct()
			{

			}

			public function __set($key, $value)
			{
				if ($key == DBProvider::ORM_CONFIRMED_TARGET)
				{
					$this->$key	= $value;
					return true;
				}
				$ormkey	= DBProvider::ORM_CONFIRMED_TARGET;
				if (empty($this->$ormkey))
				{
					return false;
				}
				$conn	= DBProvider::getInstance();
				if (!$conn->isForORM($this->$ormkey, $key))
				{
					return false;
				}
				if ($this->__m_data_loaded)
				{
					$this->__m_data_update[$key]	= $value;
				}
				else
				{
					$this->__m_data_storage[$key]	= $value;
				}
				return true;
			}

			public function __get($key)
			{
				$ormkey	= DBProvider::ORM_CONFIRMED_TARGET;
				if (empty($this->$ormkey))
				{
					return false;
				}
				if ($key == 'connection')
				{
					return DBProvider::getInstance()->getConnectionFor($this->$ormkey);
				}
				if ($this->__m_data_loaded && isset($this->__m_data_update[$key]))
				{
					return $this->__m_data_update[$key];
				}
				if (isset($this->__m_data_storage[$key]))
				{
					return $this->__m_data_storage[$key];
				}
				return false;
			}

			public function __call($method, $param)
			{
				$ormkey	= DBProvider::ORM_CONFIRMED_TARGET;
				if (empty($this->$ormkey))
				{
					return false;
				}
				$row_method	= array(
					'load',
					'loadone',
					'insert',
					'update',
					'delete'
				);
				$conn	= DBProvider::getInstance();
				if (in_array($method, $row_method))
				{
					array_unshift($param, $this->__m_data_update);
					array_unshift($param, $this->__m_data_storage);
					array_unshift($param, $this->$ormkey);
					$result	= call_user_func_array(array($conn, $method), $param);
					// 单记录操作
					if ($result['orm_row_flag'])
					{
						// 为 true时，update操作
						if ($result['orm_row_data'] === true)
						{
							foreach ($this->__m_data_update as $key => $val)
							{
								$this->__m_data_storage[$key]	= $val;
							}
							$this->__m_data_update	= array();
							$this->__m_data_loaded	= true;
						}
						// 为 false时，需要判断是否delete操作
						else if ($result['orm_row_data'] === false)
						{
							if ($method == 'delete')
							{
								$this->__m_data_storage	= array();
								$this->__m_data_update	= array();
								$this->__m_data_loaded	= true;
								$result['orm_row_data']	= true;	// ** 删除操作成功，设置返回值为true
							}
							else		// load 相关查询操作找不到记录，清空查询条件
							{
								$this->__m_data_storage	= array();
								$this->__m_data_update	= array();
							}
						}
						else if (is_array($result['orm_row_data']) && !empty($result['orm_row_data']))
						{
							foreach ($result['orm_row_data'] as $key => $val)
							{
								$this->__m_data_storage[$key]	= $val;
							}
							$this->__m_data_update	= array();
							$this->__m_data_loaded	= true;
						}
						return $result['orm_row_data'];
					}
					throw new ZQException(\ZQFramework\Lang\LANG::DB_ORM_ROW_EXEC_ERROR);
				}
				array_unshift($param, $this->$ormkey);
				return call_user_func_array(array($conn, $method), $param);
			}
		}


	}

?>