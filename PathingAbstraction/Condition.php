<?php

	namespace api\PathingAbstraction;

	/**
	 * Represents a Condition for a Statement.
	 * 
	 * @author 		Daniel Legien <d.legien@design-it.de>
	 * @package		api
	 * @subpackage	PathingAbstraction
	 */
	class Condition
	{
		private
			/**
			 * The field this conditions checks against.
			 * 
			 * @var string
			 */
			$field,
			
			/**
			 * The relation between field and value.
			 * 
			 * @var string
			 */
			$relation,
			
			/**
			 * The value the field should be checked against.
			 * 
			 * @var mixed
			 */
			$value,
			
			/**
			 * Defines whether the value should automatically be
			 * quoted or not.
			 * 
			 * @var bool
			 */
			$quoteValue;
		
		/**
		 * Creates a new Condition with the given field, relation and value.
		 * If quoteValue is true the value will be quoted (by default true)
		 * otherwise the value will not be quoted.
		 * 
		 * @param	string	$field		The field that is checked in this condition.
		 * @param	string	$relation	The relation between field and value.
		 * @param 	mixed	$value		The value the field should be checked against.
		 * @param 	bool	$quoteValue	Whether the value should be put into quotes.
		 */
		public function __construct($field, $relation, $value, $quoteValue = TRUE)
		{
			$this->field = $field;
			$this->relation = $relation;
			$this->value = $value;
			$this->quoteValue = $quoteValue;			
		}
		
		/**
		 * Returns the string representation of the Condition.
		 * 
		 * @return	string
		 */
		public function __toString()
		{
			return $this->quoteValue ? "$this->field $this->relation '$this->value'" : "$this->field $this->relation $this->value";
		}
		
	}