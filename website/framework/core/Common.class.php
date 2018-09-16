<?php
	/**
	 * Common
	 *
	 * @package Framework\Core
	 *
	 * @author 李文强 <liwenqiang@shoujids.com>
	 * @copyright 朱雀网络
	 * @version 1.0
	 */
	namespace ZQFramework\Core
	{

		class Common extends Singleton
		{

			public function __construct()
			{

			}

			public function parseDataByFilter($data, $filter=INPUT_FILTER_ALL)
			{
				if (is_array($data))
				{
					$result	= array();
					foreach ($data as $key => $value)
					{
						$result[$key]	= $this->parseDataByFilter($value, $filter);
					}
					return $result;
				}
				if (!is_string($data))
				{
					return false;
				}
				if ( @get_magic_quotes_gpc() )
				{
					$data	= stripslashes($data);
					$data	= preg_replace( "/\\\(?!&amp;#|\?#)/", "&#092;", $data );
				}
				if ($filter & INPUT_URI)
				{
					$data	= rawurldecode($data);
				}
				if ($filter & INPUT_HTML)
				{
					$data	= preg_replace("/&(?!#[0-9]+;)/s", '&amp;', 		$data );
					$data	= str_replace( "&"				, "&amp;"         , $data );
					$data	= str_replace( "<!--"			, "&#60;&#33;--"  , $data );
					$data	= str_replace( "-->"			, "--&#62;"       , $data );
					$data	= preg_replace( "/<script/i"	, "&#60;script"   , $data );
					$data	= str_replace( ">"				, "&gt;"          , $data );
					$data	= str_replace( "<"				, "&lt;"          , $data );
					$data	= str_replace( '"'				, "&quot;"        , $data );
					$data	= str_replace( "\n"				, "<br />"        , $data ); // Convert literal newlines
					$data	= str_replace( "$"				, "&#036;"        , $data );
					$data	= str_replace( "\r"				, ""              , $data ); // Remove literal carriage returns
					$data	= str_replace( "!"				, "&#33;"         , $data );
					$data	= str_replace( "'"				, "&#39;"         , $data ); // IMPORTANT: It helps to increase sql query safety.
				}
				if ($filter & INPUT_SCRIPT)
				{
					$data	= preg_replace( "/javascript/i" , "j&#097;v&#097;script", $data );
					$data	= preg_replace( "/alert/i"      , "&#097;lert"          , $data );
					$data	= preg_replace( "/about:/i"     , "&#097;bout:"         , $data );
					$data	= preg_replace( "/onmouseover/i", "&#111;nmouseover"    , $data );
					$data	= preg_replace( "/onclick/i"    , "&#111;nclick"        , $data );
					$data	= preg_replace( "/onload/i"     , "&#111;nload"         , $data );
					$data	= preg_replace( "/onsubmit/i"   , "&#111;nsubmit"       , $data );
					$data	= preg_replace( "/<body/i"      , "&lt;body"            , $data );
					$data	= preg_replace( "/<html/i"      , "&lt;html"            , $data );
					$data	= preg_replace( "/document\./i" , "&#100;ocument."      , $data );
				}
				if ($filter & INPUT_SQL)
				{
					$data	= str_replace( "'", "&#39;", $data);	// IMPORTANT: It helps to increase sql query safety.
				}
				return $data;
			}

			/**
			 * 解析ID与ROOT组成的无限级分类数组
			 *
			 * param array $data	SQL中按 root ASC, sort ASC 排充结果数组，减少生成TREE时的排序操作。
			 * param int   $root	顶级ROOT节点
			 * return array $result	排序后的数组（带有__tree_*标识）
			 */
			public static function ParseIdRootTree($data, $root=0)
			{
				$data_prepare	= array();
				$data_entries	= array();
				foreach ($data as $x)
				{
					if (isset($x['id']) && isset($x['root']) && !isset($data_prepare[$x['id']]))
					{
						$data_prepare[$x['id']]		= $x;
						$data_entries[$x['root']][]	= $x['id'];
					}
				}
				if (empty($data_prepare) || empty($data_entries[$root]))
				{
					return false;
				}
				$result	= array();
				$key	= self::_parse_id_root_tree_helper($data_prepare, $data_entries, $root, 0, $result);
				return array($key, $result);
			}

			private static function _parse_id_root_tree_helper(&$prepare, &$entries, $enter, $currdeep, &$result)
			{
				$prev	= '__tree_prev';
				$next	= '__tree_next';
				$root	= '__tree_root';
				$data	= '__tree_data';
				$deep	= '__tree_deep';
				/* 入口ID */
				$_enter_id		= NULL;
				/* 同级别中的上一个ID */
				$preview_id		= 0;
				foreach ($entries[$enter] as $id)
				{
					$data_linker		= $prepare[$id];
					$data_linker[$prev]	= $preview_id;
					$data_linker[$next]	= 0;
					$data_linker[$root]	= $enter;
					$data_linker[$deep]	= $currdeep;
					if (isset($entries[$id]) && !empty($entries[$id]))
					{
						$data_linker[$data]	= self::_parse_id_root_tree_helper($prepare, $entries, $id, $currdeep+1, $result);
					}
					else
					{
						$data_linker[$data]	= 0;
					}
					if (empty($_enter_id))
					{
						$_enter_id	= $id;
					}
					if (!empty($preview_id))
					{
						$result[$preview_id][$next]	= $id;
					}
					$preview_id		= $id;
					$result[$id]	= $data_linker;
				}
				return $_enter_id;
			}

		}//~: __END_OF_CLASS__________
	}

?>