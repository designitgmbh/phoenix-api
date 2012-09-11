<?php

	namespace api\PathingAbstraction;

	/**
	 * Represents a group of Conditions linked with OR.
	 * 
	 * @author		Daniel Legien <d.legien@design-it.de>
	 * @package		api
	 * @subpackage	PathingAbstraction
	 */
	class OrConditionGroup extends ConditionGroup
	{
		protected
			/**
			 * The join operation used for this group of conditions.
			 * 
			 * @var	string
			 */
			$joinOperation = 'OR'
		;	
	}
