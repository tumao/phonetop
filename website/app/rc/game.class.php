<?php

	if (!class_exists('AppBaseController'))
	{
		require('app-base.class.php');
	}

	class GameController extends AppBaseController
	{
		
		protected $_tagname	= 'game';

	}

?>