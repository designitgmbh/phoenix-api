<?php

	namespace api\Logging;
	
	use api\Logging\AbstractLogger;
	use api\Logging\LoggerInterface;
	
	class NullLogger extends AbstractLogger implements LoggerInterface
	{
		
		public function log($data)
		{
			// Do nothing
		}
		
	}
