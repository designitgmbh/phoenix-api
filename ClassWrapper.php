<?php

	namespace api;

	/**
	 * 
	 */
	class ClassWrapper {
				
		public function getInstance($class) {
			return new $class;
		}		
	}
