<!DOCTYPE html />
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>{$pagetitle}</title>
	<link href="/css/easy-ui/theme/common.css" rel="stylesheet" />
	<link href="/css/easy-ui/icon.css" rel="stylesheet" />
{if $_current_load_css}{foreach $_current_load_css as $_css_loaded}
	<link href="/css/{$_css_loaded}" rel="stylesheet" />
{/foreach}{/if}
	<script src="/js/jquery-1.10.2.min.js" type="text/javascript"></script>
	<script src="/js/jquery-easy-ui-1.3.4.min.js" type="text/javascript"></script>
	<script src="/js/jquery-easy-ui-1.3.4-lang-zh_CN.js" type="text/javascript"></script>
	<script src="/js/ajaxfileupload.js" type="text/javascript"></script>
{if $_current_load_script}{foreach $_current_load_script as $_script_loaded}
	<script src="/js/{$_script_loaded}" type="text/javascript"></script>
{/foreach}{/if}
</head>
<body>
	{config_load file="lang.conf"}
    {include file=$_tpl_framebody}
</body>
</html>