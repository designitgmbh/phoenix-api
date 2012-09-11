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
				// throw new Exceptions\ClassNotFoundException('Class ' . $className . ' could not be found.');
			}	
		}
	}
