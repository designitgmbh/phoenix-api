<?php

	namespace api\StructureAbstraction\StructureGatewayTable;
	
	use api\Exceptions\UnknownParameterException;
	
	
	/**
	 * Model describing a table indexes
	 * 
	 */
	class Column {
		
		protected
			/**
			 * @var string		Indicates the column name. 
			 */
			$name,

			/**
			 * @var string		Indicates the column data type. 
			 */
			$type,

			/**
			 * @var string		Indicates the collation for nonbinary string columns, or NULL for other columns. 
			 */
			$collation,

			/**
			 * @var boolean		Column may contain NULL values.
			 */
			$null,

			/**
			 * @var string		Indicates the default value that is assigned to the column.
			 */
			$default,

			/**
			 * @var string		Contains any additional information that is available about a given column.
			 */
			$extra,

			/**
			 * @var string		Indicates the privileges you have for the column.
			 */
			$privileges,

			/**
			 * @var string		Indicates any comment the column has.
			 */
			$comment
		;
		
		/**
		 * Translate data types from MySQL to PHP
		 * 
		 * @return string
		 */
		public function getPhpType() {
			if (($this->type == 'float') || ($this->type == 'double')) 
			{
				return 'float';					
			}

			if (strpos($this->type, 'int') !== FALSE) 
			{
				return 'int';
			}
			
			return 'string';
		}

		/**
		 * magic function...
		 * 
		 * @return mixed
		 */
		public function __call($name, $arguments) {
			$key = lcfirst(substr($name, 3));
			if (!property_exists($this, $key)) {
				throw new UnknownParameterException($key); 
			}
			
			return $this->$key;
		}
				
		/**
		 * magic setter
		 * 
		 * @return	void
		 */
		public function __set($key, $value) {			
			$columns = array(
				'Field'			=> function($value) { return array('name',			$value); },
				'Type'			=> function($value) { return array('type',			$value); },
				'Collation'		=> function($value) { return array('collation',		$value); },
				'Null'			=> function($value) { return array('null',			($value == 'YES')); },
				'Default'		=> function($value) { return array('default',		$value); },
				'Extra'			=> function($value) { return array('extra',			$value); },
				'Privileges'	=> function($value) { return array('privileges',	$value); },
				'Comment'		=> function($value) { return array('comment',		$value); }
			);
						
			if (!isset($columns[$key])) {
				return false;
			}
						
			$closure = $columns[$key];
			list($keyName, $checkedValue) = $closure($value);
			$this->$keyName = $checkedValue;
		}
	}