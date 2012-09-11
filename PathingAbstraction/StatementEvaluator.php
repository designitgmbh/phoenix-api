<?php

	namespace api\PathingAbstraction;

	/**
	 * Interface for StatementEvaluators. Each Evaluator has to implement
	 * a constructor and a method used for evaluation. An evaluator is
	 * used to translate a Statement to a specific database dialect.
	 * 
	 * @author 		Daniel Legien <d.legien@design-it.de>
	 * @package		api
	 * @subpackage	PathingAbstraction
	 * 
	 */
	interface StatementEvaluator
	{
		/**
		 * Creates new StatementEvaluators.
		 */		
		public function __construct();
		
		/**
		 * Evaluates the provided Statement.
		 * 
		 * @param	Statement	$statement	The statement that should be evaluated.
		 * 
		 * @return 	string
		 */
		public function evaluate(Statement $statement);			
	}
	
