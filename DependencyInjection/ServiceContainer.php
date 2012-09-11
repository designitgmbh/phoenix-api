<?php

	namespace api\DependencyInjection;
	
	use api\DependencyInjection\ServiceContainerInterface;
	use api\ServiceManager;

	/**
	 * 
	 * @author 		Fabien Potencier
	 * @package		api
	 * @subpackage	DependencyInjection
	 * 
	 * @todo		documentation
	 */
	class ServiceContainer implements ServiceContainerInterface
	{
		protected 
			$parameters = array(),
			$services = array(),
			$manager			
		;
		
	
		public function __construct(ServiceManager $manager, array $parameters = array())
		{
			$this->setParameters($parameters);
			$this->setService('service_container', $this);
			$this->manager = $manager;
		}
		
		public function setParameters(array $parameters)
		{
			foreach($parameters as $key => $value)
			{
				$this->setParameter($key, $value);
			}	
		}
		
		public function setParameter($name, $value)
		{
			$this->parameters[strtolower($name)] = $value;
		}
		
		public function addParameters(array $parameters)
		{
			$this->setParameters(array_merge($this->parameters, $parameters));	
		}
		
		public function getParameters()
		{
			return $this->parameters;
		}
		
		public function getParameter($name)
		{
			if(!$this->hasParameter($name))
			{
				throw new \InvalidArgumentException(sprintf('Parameter "%s" is not defined.', $name));
			}
			
			return $this->parameters[strtolower($name)];
		}
		
		public function hasParameter($name)
		{
			return array_key_exists(strtolower($name), $this->parameters);
		}
		
		public function setService($id, $service)
		{
			$this->services[$id] = $service;	
		}
		
		public function hasService($id)
		{
			return isset($this->services[$id]) || method_exists($this, 'get'.self::camelize($id).'Service');
		}
		
		public function getService($id)
		{
			if(isset($this->services[$id]))
			{
				return $this->services[$id];
			}
			
			if(method_exists($this, $method = 'get'.self::camelize($id).'Service'))
			{
				return $this->$method();
			}
			
			throw new \InvalidArgumentException(sprintf('The service "%s" does not exist.', $id));
		}
		
		public function getServiceIds()
		{
			$ids = array();
			$r = new \ReflectionClass($this);
			foreach($r->getMethods() as $method)
			{
				if (preg_match('/^get(.+)Service$/', $method->getName(), $match))
				{
					$ids[] = self::underscore($match[1]);
				}	
			}
			
			return array_merge($ids, array_keys($this->services));
		}
		
		static public function camelize($id)
		{
			return preg_replace(array('/(^|_|-)+(.)/e', '/\.(.)/e'), array("strtoupper('\\2')", "'_'.strtoupper('\\1')"), $id);
		}		
	}

