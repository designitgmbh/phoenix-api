<?php

	namespace api\DataAbstraction;

	/**
	 * The TableDataGatewayFilter represents a condition by which the
	 * datasets should be filtered.
	 * 
	 * @author 		Daniel Legien <d.legien@design-it.de>
	 * @package		api
	 * @subpackage	DataAbstraction
	 */
	class TableDataGatewayFilter
	{
		private
			/**
			 * The field to check against.
			 * 
			 * @var	string
			 */
			$field,
			
			/**
			 * The relation between the field and the value.
			 * 
			 * @var string
			 */
			$relation,
			
			/**
			 * The value which the field should be checked against.
			 * 
			 * @var mixed
			 */
			$value
		;
		
		/**
		 * Creates a new TableDataGatewayFilter with the given field,
		 * relation and value.
		 * 
		 * @param	string	$field		The field to check against.
		 * @param	string	$relation	The relation between field and value.
		 * @param	mixed	$value		The value to check the field against.
		 */	
		public function __construct($field, $relation, $value)
		{
			$this->field = $field;
			$this->relation = $relation;
			$this->value = $value;
		}
		
		/**
		 * Returns the field to check against.
		 * 
		 * @return	string
		 */
		public function getField()
		{
			return $this->field;
		}
		
		/**
		 * Returns the relation between field and value.
		 * 
		 * @return	string
		 */
		public function getRelation()
		{
			return $this->relation;
		}
		
		/**
		 * Returns the value the field should be checked against.
		 * 
		 * @return mixed
		 */
		public function getValue()
		{
			return $this->value;
		}
	}
