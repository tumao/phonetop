<?php

	namespace ZQFramework\Core;
	require('../bootstrap.php');
	
	try
	{
		Application::getInstance()->startup();
	}
	catch (ZQException $ex)
	{
		$ex->displayErrorPage();
	}
	catch (Exception $ex)
	{
		echo $ex->getMessage();
	}

?>