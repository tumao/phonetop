<?php
	/**
	 * ZQException
	 *
	 * @package Framework\Core
	 *
	 * @author 李文强 <liwenqiang@shoujids.com>
	 * @copyright 朱雀网络
	 * @version 1.0
	 */
	namespace ZQFramework\Core
	{
	
		class ZQException extends \Exception
		{
			
			public function __construct($message, $code=0, $previous=NULL)
			{
				parent::__construct($message, $code, $previous);
			}
			
			public function __toString()
			{
				return __CLASS__ . ": [{$this->code}]: {$this->message}";
			}
			
			public function displayErrorPage()
			{
				echo $this->message;
			}
			
		}//~: __END_OF_CLASS__________
	}

?>