<?php

	namespace api\DataAbstraction\Container;

	use api\PathingAbstraction\Statement;

	class ContainerGateway
	{
		private
			$paths
		;
		
		public function __construct()
		{
			$args = func_get_args();
			
			foreach($args as $path)
			{
				$this->paths[] = $path;
			}	
		}
		
		public function get()
		{
			$stmt = new Statement;
			
			
		}
		
	}
