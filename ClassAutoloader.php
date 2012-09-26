<?php

	namespace api;

	/**
	 * 
	 */
	class ClassAutoloader {
		protected $closures = array();
		
		/**
		 * 
		 */
		public function __construct($closures=array()) {
			foreach((array) $closures as $key => $value) {
				$this->setPathClosure($key, $value);
			}
			spl_autoload_register(array($this, 'loader'));
		}
		
		/**
		 * 
		 */
		public function callClosure($closureName, $className) {
			$func = $this->getPathClosureByName($closureName);
			return $func($className);
		}
		
		/**
		 * 
		 */
		public function getPathClosureByName($closureName) {
			if (!isset($this->closures[$closureName])) {
				throw new \Exception("Unknown closure closureName: ". $closureName, 1);
			}
			return $this->closures[$closureName];
		}
		
		/**
		 * 
		 */
		protected function setPathClosure($name, $code) {
			$this->closures[$name] = $code;
		}
		
		/**
		 * 
		 */
		protected function loader($className) {
			foreach ($this->closures as $value) {
				$filename = $value($className);
				if (is_readable($filename)) {
					include_once($filename);
					break;
				}
			}
			
			if (
				class_exists($className, false) === false
				&&
				interface_exists($className, false) === false
			) {
				$filename = array();
				foreach ($this->closures as $name => $value) {
					$filename[$name] = $value($className);
				}
				
				throw new Exceptions\ClassNotFoundException(
					'Class ' . '<pre>' . $className . '</pre>' . ' could not be found.'
					. PHP_EOL
					. '<pre>' . print_r($filename, true) .'</pre>'
				);
			}	
		}
	}
