<?php

	namespace api\DependencyInjection;
	
	/**
	 * The ServiceReference represents a reference to a service and is
	 * used to reference other service in the ServiceContainerBuilder.
	 * 
	 * @author Fabien Potencier
	 */
	class ServiceReference
	{
		protected
			/**
			 * The identification of the reference.
			 * 
			 * @var mixed
			 */
			$id = NULL;
		
		/**
		 * Creates a new ServiceReference with the given id.
		 * 
		 * @param	mixed	$id	The identification of the representation.
		 */	
		public function __construct($id)
		{
			$this->id = $id;
		}
		
		/**
		 * Returns the string representation of the reference.
		 * 
		 * @return	string
		 */
		public function __toString()
		{
			return (string)$this->id;
		}
	}
	
