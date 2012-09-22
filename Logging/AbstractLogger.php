<?php

	namespace api\Logging;

	/**
	 * The abstract Logger class.
	 * 
	 * @author	Daniel Legien <d.legien@design-it.de>
	 */
	abstract class AbstractLogger
	{
		private
			/**
			 * The model of the object.
			 * 
			 * @var	string
			 */
			$model,
			
			/**
			 * The primary key of the object.
			 * 
			 * @var	string
			 */
			$pk,
			
			/**
			 * The id of the user.
			 * 
			 * @var	int
			 */
			$uid
		;
		
		
		
		/**
		 * Sets model to the given value.
		 * 
		 * @param	string	$model	
		 */
		public function setModel($model) 
		{
			$this->model = (string)$model;
		}
		
		/**
		 * Sets pk to the given value.
		 * 
		 * @param	string	$pk	
		 */
		public function setPk($pk) 
		{
			$this->pk = (string)$pk;
		}
		
		/**
		 * Sets uid to the given value.
		 * 
		 * @param	int	$uid	
		 */
		public function setUid($uid) 
		{
			$this->uid = (int)$uid;
		}
		
		/**
		 * Returns the value of model.
		 * 
		 * @return	string
		 */
		public function getModel() 
		{
			return (string)$this->model;
		}
		
		/**
		 * Returns the value of pk.
		 * 
		 * @return	string
		 */
		public function getPk() 
		{
			return (string)$this->pk;
		}
		
		/**
		 * Returns the value of uid.
		 * 
		 * @return	int
		 */
		public function getUid() 
		{
			return (int)$this->uid;
		}
	}