<?php

	namespace api\PathingAbstraction;

	/**
	 * Represents a group of Conditions for a Statement.
	 * 
	 * @author 		Daniel Legien <d.legien@design-it.de>
	 * @package		api
	 * @subpackage	PathingAbstraction
	 * 
	 */
	class ConditionGroup
	{		
		protected
			/**
			 * An array of all the child conditions associated with
			 * this group.
			 * 
			 * @var array
			 */
			$children
		;
		
		/**
		 * Creates a new ConditionGroup
		 */
		public function __construct()
		{
			$this->children = array();
			
			$children = func_get_args();
			foreach($children as $child) {
				$this->set($child);
			}
		}
		
		/**
		 * Adds a new child Condition to the group. If the parameter is neither a Condition
		 * nor another ConditionGroup an Exception is thrown.
		 * 
		 * @param	mixed	$child	The child Condition is being added.
		 * 
		 * @return	ConditionGroup
		 * @throws	\Exception
		 */
		public function set($child)
		{
			if($child instanceof ConditionGroup || $child instanceof Condition)
			{
				$this->children[] = $child;
				return $this;				
			}
			throw new \Exception('ConditionGroup.set(): A ConditionGroup can only accept other ConditionGroups or Conditions as children.');		
		}
		
		/**
		 * Returns an array of all the child Conditions associated with
		 * this ConditionGroup.
		 * 
		 * @return	array
		 */
		public function getChildren()
		{
			return $this->children;
		}
		
		/**
		 * Returns the join operation for this ConditionGroup or throws an 
		 * Exception if none is defined.
		 * 
		 * @return	string
		 * @throws	\Exception
		 */
		public function getJoinOperation()
		{
			return $this->joinOperation;
		}
		
		/**
		 * Sets the join operation to the provided join operation.
		 * 
		 * @param	string	$joinOperation	The join operation for this group.
		 * 
		 * @return	ConditionGroup
		 */		
		public function setJoinOperation($joinOperation)
		{
			$this->joinOperation = $joinOperation;
			return $this;	
		}
		
		/**
		 * Returns the string representation of this ConditionGroup by
		 * retrieving the string representations of all its children.
		 * 
		 * @return	string
		 */		
		public function __toString()
		{
			foreach($this->children as $child)
			{
				if($child instanceof Condition)
				{
					$statements[] = (string)$child;
				}
				elseif($child instanceof ConditionGroup)
				{
					$statements[] = '('. $child .')';					
				}
			}
			return '' . join(' ' . $this->getJoinOperation() . ' ', $statements) . '';			
		}	
	}

