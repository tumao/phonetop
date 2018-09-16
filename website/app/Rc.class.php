<?php

	class RcControl extends ABaseControl
	{
		
		
		public function init()
		{
			// if (isset($_REQUEST['id']))
			// {
			// 	$arrid	= $_REQUEST['id'];
			// 	if (!is_array($arrid))
			// 	{
			// 		$arrid	= explode(',', $arrid);
			// 	}
			// 	foreach ($arrid as $id)
			// 	{
			// 		$id	= intval($id);
			// 		if (empty($id)) continue;
			// 		$this->_id_filter[]	= $id;
			// 	}
			// }
		}
		
		public function free()
		{
			
		}

		public function listAction(){
			echo 111;
		}
		
		
	}

?>