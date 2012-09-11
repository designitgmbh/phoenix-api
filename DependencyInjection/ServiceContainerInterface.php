<?php

	namespace api\DependencyInjection;

	/**
	 * 
	 * @author Fabien Potencier
	 * 
	 * TODO: documentation
	 */
	interface ServiceContainerInterface
	{
		public function setParameters(array $parameters);
		
		public function addParameters(array $parameters);
		
		public function getParameters();
		
		public function getParameter($name);
		
		public function setParameter($name, $value);
		
		public function hasParameter($name);
		
		public function setService($id, $service);
		
		public function getService($id);
		
		public function hasService($name);
	}
	
