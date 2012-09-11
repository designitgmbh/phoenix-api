<?php

	namespace api\DependencyInjection;
	
	use api\DependencyInjection\ServiceContainer;
	
	/**
	 * 
	 * @author Fabien Potencier
	 * 
	 * TODO: documentation
	 */
	class ServiceContainerBuilder extends ServiceContainer
	{
		protected
			$definitions = array(),
			$loading = array();
			
		public function hasService($id)
		{
			return isset($this->definitions[$id]) || parent::hasService($id);	
		}	
		
		public function getService($id)
		{
			try
			{
				return parent::getService($id);
			}
			catch(\InvalidArgumentException $e)
			{
				if(isset($this->loading[$id]))
				{
					throw new \LogicException(sprintf('The service "%s" has a circular reference to itself.' . $e, $id));
				}
				
				$definition = $this->getServiceDefinition($id);
				
				if(!$definition->hasFailingConstructor())
				{
					$this->loading[$id] = TRUE;
				}
				
				if($definition->isShared())
				{
					$service = $this->services[$id] = $this->createService($definition);
				}
				else
				{
					$service = $this->createService($definition);
				}
				
				unset($this->loading[$id]);
				
				return $service;
			}
		}
		
		public function getServiceIds()
		{
			return array_unique(array_merge(array_keys($this->getServiceDefinitions()), parent::getServiceIds()));
		}
			
		public function register($id, $class)
		{
			return $this->setServiceDefinition($id, new ServiceDefinition($class));
		}
		
		public function setServiceDefinitions(array $definitions)
		{
			foreach($definitions as $id => $definition)
			{
				$this->setServiceDefinition($id, $definition);
			}
		}
		
		public function getServiceDefinitions()
		{
			return $this->definitions;
		}
		
		public function setServiceDefinition($id, ServiceDefinition $definition)
		{
			return $this->definitions[$id] = $definition;
		}
		
		public function hasServiceDefinition($id)
		{
			return array_key_exists($id, $this->definitions);	
		}
					
		public function getServiceDefinition($id)
		{
			if(!$this->hasServiceDefinition($id))
			{
				if(!$this->resolveServiceDefinition($id))
				{
					throw new \InvalidArgumentException(sprintf('The service definition "%s" does not exist.', $id));
				}
			}
			
			return $this->definitions[$id];
		}
		
		protected function resolveServiceDefinition($id)
		{
			if($this->manager->hasService($id))
			{
				$this->manager->getDefinition($id);
				return TRUE;
			}
			return FALSE;				
		}			
		
		private function constructService($definition, $arguments, \ReflectionClass $reflection)
		{
			if(!is_null($definition->getConstructor()))
			{
				return call_user_func_array(array($definition->getClass(), $definition->getConstructor()), $arguments);
			}
			
			return is_null($reflection->getConstructor()) ? $reflection->newInstance() : $reflection->newInstanceArgs($arguments);
		}
		
		private function configureService($definition, $service)
		{
			if($callable = $definition->getConfigurator())
			{
				if(is_array($callable) && is_object($callable[0]) && $callable[0] instanceof ServiceReference)
				{
					$callable[0] = $this->getService((string)$callable[0]);
				}
				elseif(is_array($callable))
				{
					$callable[0] = $this->resolveValue($callable[0]);					
				}
				
				if (!is_callable($callable))
				{
					throw new \InvalidArgumentException(sprintf('The configure callable for class "%s" is not a callable.', get_class($service)));
				}
				
				call_user_func($callable, $service);
			}			
		}
					
		protected function createService(ServiceDefinition $definition)
		{
			if(!is_null($definition->getFile()))
			{
				require_once $this->resolveValue($definition->getFile());
			}
			
			$r = new \ReflectionClass($this->resolveValue($definition->getClass()));

			$arguments = $this->resolveServices($this->resolveValue($definition->getArguments()));
			
			$service = $this->constructService($definition, $arguments, $r);
			
			foreach($definition->getMethodCalls() as $call)
			{
				call_user_func_array(array($service, $call[0]), $this->resolveServices($this->resolveValue($call[1])));				
			}
			
			$this->configureService($definition, $service);
			
			return $service;
		}		
		
		public function resolveValue($value)
		{
			if(is_array($value))
			{
				$args = array();
				
				foreach($value as $k => $v)
				{
					$args[$this->resolveValue($k)] = $this->resolveValue($v);
				}
				
				$value = $args;
			}
			elseif(is_string($value))
			{
				if(preg_match('/^%([^%]+)%$/', $value, $match))
				{
					if (!$this->hasParameter($name = strtolower($match[1])))
					{
						throw new \RuntimeException(sprintf('The parameter "%s" must be defined.', $name));
					}
					
					$value = $this->getParameter($name);
				}
				else
				{					
					$value = str_replace('%%', '%', preg_replace_callback('/(?<!%)(%)([^%]+)\1/', array($this, 'replaceParameter'), $value));
				}
				
				if(is_string($value) && preg_match('/^%%([^%]+)%%$/', $value, $match))
				{
					$value = new ServiceReference($match[1]);					
				}
			}

			return $value;
		}
		
		public function resolveServices($value)
		{
			if(is_array($value))
			{
				$newvalue = array();
				foreach($value as $v)
				{
					$newvalue[] = $this->resolveServices($v);	
				}
				$value = $newvalue;
			}
			elseif(is_object($value) && $value instanceof ServiceReference)
			{
				$value = $this->getService((string)$value);
			}
			
			return $value;
		}	
		
		public function replaceParameter($match)
		{
			if(!$this->hasParameter($name = strtolower($match[2])))
			{
				throw new \RuntimeException(sprintf('The parameter "%s" must be defined.', $name));
			}
			
			return $this->getParameter($name);
		}
					
	}
