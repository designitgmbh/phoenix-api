<?php

	namespace api\DataAbstraction;
	
	use api\Exceptions\InvalidIndexException;
	
	class ObjectBuilder
	{
		private
			$footprints = array(),
			$reflections = array()
		;
	
		private function getParamsFromFootprint($footprint, $object, $class)
		{
			$params = NULL;
				
			if(count($footprint) > 1)
			{
				$params = array();
				foreach($footprint as $key)
				{
					$params[$key] = $object[$key];
				}
			}
			elseif(count($footprint) == 1 && $footprint[0] == 'args')
			{
				$params = array($object);
			}
			elseif(count($footprint) == 1 && $footprint[0] != 'args')
			{
				if(!isset($object[$footprint[0]]))
				{				
					throw new \RuntimeException('ObjectBuilder.wrap(): Failed wrapping ' . $class . ' because a required constructor parameter was not passed');
				}
				$params = array($footprint[0] => $object[$footprint[0]]);
			}	
			
			return $params;
		}
	
		public function wrap($class, $object)
		{
			$reflection = $this->getReflection($class);
			$footprint = $this->getFootprint($class);
			$params = $this->getParamsFromFootprint($footprint, $object, $class);
												
			if(!is_null($params))
			{
				$obj = $reflection->newInstanceArgs((array)$params);
				$additionalProperties = array_diff_assoc($object, $params);	
			}
			else
			{
				$obj = $reflection->newInstance();
				$additionalProperties = $object;
			}
			
			foreach($additionalProperties as $key => $value)
			{
				$methodName = 'set'.ucwords($key);					
				$obj->$methodName($value);	
			}			

			return $obj;
		}		
		
		public function takeFootprint($class)
		{
			$reflectionClass = $this->getReflection($class);
			if($reflectionClass->hasMethod('__construct'))
			{
				$params = array();
				$r = new \ReflectionMethod($class, '__construct');
				foreach($r->getParameters() as $param)
				{
					$params[] = $param->getName();
				}
				$this->setFootprint($class, $params);
			}
			else
			{
				$this->setFootprint($class, array());	
			}			
		}
		
		public function takeReflection($class)
		{
			$this->setReflection($class, new \ReflectionClass($class));	
		}
		
		public function getReflection($class)
		{
			if(!$this->hasReflection($class))
			{
				$this->takeReflection($class);
			}
			return $this->reflections[$class];	
		}
		
		public function setReflection($class, $reflection)
		{
			$this->reflections[$class] = $reflection;
		}
		
		public function hasReflection($class)
		{
			return key_exists($class, $this->reflections);	
		}
		
		public function getFootprint($class)
		{
			if(!$this->hasFootprint($class))
			{
				$this->takeFootprint($class);
			}
			return $this->footprints[$class];	
		}
		
		private function setFootprint($class, $parameters)
		{
			$this->footprints[$class] = $parameters;
		}
		
		public function hasFootprint($class)
		{
			return key_exists($class, $this->footprints);	
		}
	}
	

		