<?php

	namespace api\StructureAbstraction;
	
	
	/**
	 * Model describing a tables name an type
	 * 
	 */
	class StructureGatewayTable {
		
		protected
		
			/**
			 * @var string	name of the table
			 */
			$name,
			
			/**
			 * @var	string	type of the table
			 */
			$type
		;
		
		/**
		 * 
		 * @return	string	name of the table
		 */
		public function getName() {
			return $this->name;
		}

		/**
		 * 
		 * @return	string	type of the table
		 */		
		public function getType() {
			return $this->type;
		}
		
		/**
		 * magic setter
		 * 
		 * @return	void
		 */
		public function __set($fullkey, $value) {
			$columns = array(
				'Tables_in_' => 'name',
				'Table_type' => 'type',
			);
			
			$key = substr($fullkey, 0, 10);			
			
			if (!isset($columns[$key])) {
				throw new \Exception("Unknown Parameter", 1);
			}
			
			$this->{$columns[$key]} = $value;
		}
	}