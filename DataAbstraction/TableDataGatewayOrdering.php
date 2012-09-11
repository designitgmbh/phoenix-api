<?php

	namespace api\DataAbstraction;

	/**
	 * The TableDataGatewayOrdering represents an ordering that is
	 * applied to the results of a query of the TableDataGateway.
	 * 
	 * @author		Daniel Legien <d.legien@design-it.de>
	 * @package		api
	 * @subpackage	DataAbstraction
	 */
	class TableDataGatewayOrdering
	{		
		private
			/**
			 * The field to order by.
			 * 
			 * @var string
			 */
			$field,
			
			/**
			 * The direction to order in.
			 * 
			 * @var string
			 */
			$direction
		;
		
		/**
		 * Creates a new TableDataGatewayOrdering with the given field
		 * and direction. If the direction is omitted the default
		 * direction ASCending is assumed.
		 * 
		 * @param	string	$field		The field to order by.
		 * @param	string	$direction	The direction to order in.
		 */				
		public function __construct($field, $direction = 'ASC')
		{
			$this->field = $field;
			$this->direction = $direction;
		}
		
		/**
		 * Returns the field to order by.
		 * 
		 * @return	string
		 */
		public function getField()
		{
			return $this->field;
		}
		
		/**
		 * Returns the direction to order in.
		 * 
		 * @return	string
		 */
		public function getDirection()
		{
			return $this->direction;
		}
		
	}
