<?php
	
	namespace api;

	/**
	 * 
	 */
	class ContainerAbstract implements \Iterator {
		
		private $content = array();
		
		/* Container-Methods */
				
		protected function _addElement($newElement) {
			$this->content[] = $newElement; 
		}

		protected function _getAllElements() {
			return $this->content; 
		}
		
		public function getByIndex($index) {
			if (isset($this->content[$index]) === false) {
				throw new \Exception("undefined index", 1);
			}
			return $this->content[$index];
		}
		
		/* Iterator-Methods */
	
	    public function rewind() {			
			reset($this->content);
	    }
	
	    public function current() {
	        return current($this->content);
	    }
	
	    public function key() {
	    	return key($this->content);
	    }
	
	    public function next() {
	        return next($this->content);
	    }
	
	    public function valid() {
	        return isset(
	        	$this->content[$this->key()]
			);
	    }
	}