<?php

	namespace api\StructureAbstraction\StructureGatewayTable;
	
	use api\Exceptions\UnknownParameterException;
	use api\StructureAbstraction\StructureGatewayTable\IndexColumn;
	
	
	/**
	 * Model describing a table column
	 * 
	 */
	class Index {
		
		protected
			/**
			 * @var string		The name of the index. If the index is the primary key, the name is always PRIMARY. 
			 */
			$name,

			/**
			 * @var boolean		0 if the index cannot contain duplicates, 1 if it can. 
			 */
			$unique,

			/**
			 * @var string		An estimate of the number of unique values in the index.
			 */
			$cardinality,

			/**
			 * @var string		Indicates how the key is packed. NULL if it is not. 
			 */
			$packed,

			/**
			 * @var string		The index method used (BTREE, FULLTEXT, HASH, RTREE). 
			 */
			$index_type,

			/**
			 * @var string		Information about the index not described in its own column, such as disabled if the index is disabled. 
			 */
			$comment,

			/**
			 * @var [api\StructureAbstraction\StructureGatewayTable\Column]
			 */
			$indexColumns = array()
		;
		
		/**
		 * set one IndexColumn
		 * 
		 * @param	api\StructureAbstraction\StructureGatewayTable\Column $column
		 * @return	void
		 */
		public function setIndexColumn(Column $column)
		{
			$this->indexColumns[$column->getName()] = $column;
		}
		
		/**
		 * set multiple Columns
		 * 
		 * @param	[api\StructureAbstraction\StructureGatewayTable\Column] $columns
		 * @return	void
		 */
		public function setIndexColumns($columns)
		{
			foreach($columns as $column) {
				$this->setIndexColumn($column);
			}
		}
		
		/**
		 * make this object compareable by array_unique...
		 * 
		 * @return string
		 */
		public function __toString()
		{
		    return $this->name;
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
		 * magic setter...
		 * 
		 * @return	void
		 */
		public function __set($key, $value) {
			$columns = array(
				'Key_name'		=> function($value) {
					return array(
						'name',
						($value == 'PRIMARY') ? 'primary' : $value 
					);
				},
				'Non_unique'	=> function($value) { return array('unique',		!(bool)$value); },
				'Cardinality'	=> function($value) { return array('cardinality',	$value); },
				'Packed'		=> function($value) { return array('packed',		$value); },
				'Index_type'	=> function($value) { return array('index_type',	$value); },
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