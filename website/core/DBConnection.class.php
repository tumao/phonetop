<?php

	class DBConnection extends ASingleton
	{
		protected static $_connection	= NULL;
		protected static $_redisConn	= NULL;
		
		protected $connection	= NULL;
		protected $rdconn		= NULL;
		protected $tablename	= NULL;
		protected $fieldconf	= array();
		
		protected function __construct()
		{
			if (!self::$_connection)
			{
				$dbconf	= require(CONF.'service'._.'db-'.ENV_TAG__.'.php');
				$dsn	= 'mysql:host='.$dbconf['hostaddr'].';port='.$dbconf['portnumb'].';dbname='.$dbconf['database'];
				self::$_connection	= new PDO($dsn, $dbconf['username'], $dbconf['password'], array(
					PDO::ATTR_CASE		=> PDO::CASE_LOWER,
					PDO::ATTR_ERRMODE	=> PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE	=> PDO::FETCH_ASSOC
				));
				self::$_connection->exec("SET NAMES UTF8");
				// ** redis连接
				$rdconf	= require(CONF.'service'._.'redis-'.ENV_TAG__.'.php');
				self::$_redisConn	= new Redis();
				self::$_redisConn->connect($rdconf['hostname'], $rdconf['portnumb']);
				self::$_redisConn->select($rdconf['selected']);
			}
			if (!$this->fieldconf)
			{
				$called	= get_called_class();
				$sql	= "DESC `".$called::TABLE."`";
				$result	= self::$_connection->query($sql);
				$fields	= $result->fetchAll();
				foreach ($fields as $row)
				{
					$this->fieldconf[$row['field']]	= empty($row['key']) ? true : $row['key'];
				}
				$this->tablename	= $called::TABLE;
			}
			$this->rdconn		= self::$_redisConn;
			$this->connection	= self::$_connection;
		}
		
		public function &getConnection()
		{
			return $this->connection;
		}
		
		public function &getRdconn()
		{
			return $this->rdconn;
		}
		
		public function countByQuery($where)
		{
			$sql	= "SELECT count(*) FROM `{$this->tablename}` WHERE {$where}";
			$result	= $this->connection->query($sql);
			$info	= $result->fetch();
			$count	= array_pop($info);
			return  $count;
		}
		
		public function listByQuery($where, $order, $limit=0, $offset=0, $calling=NULL)
		{
			$sql	= "SELECT * FROM `{$this->tablename}` WHERE {$where} ORDER BY {$order}";
			$limit	= intval($limit);
			$offset	= intval($offset);
			$list	= array();
			if ($offset > 0)
			{
				$sql .= " LIMIT {$offset},";
				if ($limit > 0)
				{
					$sql  .= "{$limit}";
				}
				else
				{
					$sql .= "25";
				}
			}
			elseif ($limit > 0)
			{
				$sql .= " LIMIT {$limit}";
			}
			$result	= $this->connection->query($sql);
			$index	= 0;
			while ($row = $result->fetch())
			{
				$index++;
				if (!empty($calling))
				{
					$row	= call_user_func_array($calling, array($index, $row));
				}
				$list[]	= $row;
			}
			return $list;
		}
		
		public function insert($info)
		{
			$fieldary	= array();
			$valueary	= array();
			foreach ($this->fieldconf as $field => $set)
			{
				if (!isset($info[$field]))
				{
					continue;
				}
				$fieldary[]	= $field;
				$valueary[]	= $this->connection->quote($info[$field]);
			}
			if (empty($fieldary)) return true;
			$sql  = "INSERT INTO `".$this->tablename."`(`";
			$sql .= implode("`, `", $fieldary);
			$sql .= "`) VALUE(";
			$sql .= implode(", ", $valueary);
			$sql .= ")";
			$succ = $this->connection->exec($sql);
			return $succ === true;
		}
		
		public function errorInfo()
		{
			return $this->connection->errorInfo();
		}
		
	}

?>