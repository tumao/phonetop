<?php
	
	define('ENV_TAG__',	'develop');
	
	define('_',		DIRECTORY_SEPARATOR);
	define('BASE',	__DIR__._);
	
	define('CONF',	BASE.'conf'._);
	define('CORE',	BASE.'core'._);
	define('APP',	BASE.'app'._ );
	define('ORM',	BASE.'orm'._ );
	
	foreach (glob(CONF.'*.inc.php') as $_file_t_conf)
	{
		require($_file_t_conf);
	}
	require(CORE.'Singleton.class.php');
	require(CORE.'Base.class.php');
	require(CORE.'Application.class.php');
	spl_autoload_register('Application::autoload');

?>