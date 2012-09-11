<?php

	namespace api\DependencyInjection;
	
	/**
	 * The ServiceDefintion represents a service and is used for
	 * the dependency injection.
	 * 
	 * @author Fabien Potencier
	 * 
	 * TODO: documentation
	 */
	class ServiceDefinition
	{
		protected
			$class 				= NULL,
			$constructor 		= NULL,
			$file 				= NULL,
			$configurator 		= NULL,						
			$calls 				= array(),			
			$args 				= array(),
			$shared 			= TRUE,
			$failingConstructor	= FALSE
		;
		
		public function __construct($class, array $args = array())
		{
			$this->class = $class;
			$this->args = $args;
		}
		
		public function setConstructor($method)
		{
			$this->constructor = $method;
			
			return $this;
		}
		
		public function getConstructor()
		{
			return $this->constructor;
		}
		
		public function setClass($class)
		{
			$this->class = $class;
			
			return $this;
		}
		
		public function getClass()
		{
			return $this->class;
		}
		
		public function setArguments(array $args)
		{
			$this->args = $args;
			
			return $this;
		}
		
		public function addArgument($argument)
		{
			if(is_string($argument) && preg_match('/^%%([^%]+)%%$/', $argument, $match))
			{
				$this->args[] = new ServiceReference($match[1]);
			}
			else
			{
				$this->args[] = $argument;	
			}			
			
			return $this;	
		}
		
		public function getArguments()
		{
			return $this->args;
		}
		
		public function setMethodCalls(array $calls = array())
		{
			$this->calls = array();
			foreach($calls as $call)
			{
				$this->addMethodCall($call[0], $call[1]);
			}
			
			return $this;
		}
		
		public function addMethodCall($method, array $args = array())
		{
			$this->calls[] = array($method, $args);
			
			return $this;
		}
		
		public function getMethodCalls()
		{
			return $this->calls;
		}
		
		public function setFile($file)
		{
			$this->file = $file;
			
			return $this;
		}
		
		public function getFile()
		{
			return $this->file;
		}
		
		public function setShared($shared)
		{
			$this->shared = (Boolean)$shared;
			
			return $this;
		}
		
		public function isShared()
		{
			return $this->shared;
		}

		public function setConfigurator($callable)
		{
			$this->configurator = $callable;
			
			return $this;
		}

		public function getConfigurator()
		{
			return $this->configurator;
		}
		
		public function setFailingConstructor($failingConstructor)
		{
			$this->failingConstructor = $failingConstructor;
		}
		
		public function hasFailingConstructor()
		{
			return $this->failingConstructor;
		}

	}
