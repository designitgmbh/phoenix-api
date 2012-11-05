<?php

	namespace api\PathingAbstraction;

	/**
	 * The SQLStatementEvaluator evaluates a Statement using the SQL dialect.
	 * 
	 * @author		Daniel Legien <d.legien@design-it.de>
	 * @package		api
	 * @subpackage	PathingAbstraction
	 */
	class SQLStatementEvaluator implements StatementEvaluator
	{
		private
			/**
			 * An array of the joins known to this evaluator.
			 * 
			 * @var	array
			 */
			$joins;
		
		/**
		 * Creates a new SQLStatementEvaluator object and defines the join types it
		 * is capable of. The join types should be those defined in the Statement
		 * class.
		 */
		public function __construct()
		{
			$this->joins = array(
				Statement::$LEFT_JOIN => 'LEFT',
				Statement::$RIGHT_JOIN => 'RIGHT',
				Statement::$CROSS_JOIN => 'CROSS',
				Statement::$INNER_JOIN => 'INNER'
			);		
		}
		
		/**
		 * Performs an evaluation of the provided Statement's fields. This method is
		 * used in the select statement evaluation.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be
		 * 									based on.
		 * 
		 * @return	string
		 */
		private function evaluateWhat(Statement $statement)
		{
			$fields = $statement->getFields();
			
			foreach($fields as $alias => $what)
			{
				$whats[] = $alias == $what ? $what : "$what AS $alias";				
			}
			
			return count($fields) > 0 ? implode(', ', $whats) : '*';
		}
		
		/**
		 * Performs an evaluation of the provided Statement's fields. This method is
		 * used in the insert statement evaluation.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be
		 * 									based on.
		 * 
		 * @return	string
		 */		
		private function evaluateFields(Statement $statement)
		{
			$fields = $statement->getFields();
			
			return '`'.implode('`,`',$fields).'`';
		}

		/**
		 * Performs an evaluation of the provided Statement's sets. This method is
		 * used in the update statement evaluation.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be
		 * 									based on.
		 * 
		 * @return	string
		 */				
		private function evaluateSets(Statement $statement)
		{
			$sets = $statement->getSets();
			
			foreach($sets as $field => $value)
			{
				$s[] = "$field = $value";
			}
			return implode(', ', $s);
		}
		
		/**
		 * Performs an evaluation of the provided Statement's values. This method is
		 * used in the insert statement evaluation.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be
		 * 									based on.
		 * 
		 * @return	string
		 */				
		private function evaluateValues(Statement $statement)
		{
			$values = $statement->getValues();
			
			return implode(',',$values);
		}
		
		/**
		 * Performs an evaluation of the provided Statement's tables. This method is
		 * used in all statement evaluations.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be
		 * 									based on.
		 * 
		 * @return	string
		 */				
		private function evaluateTables(Statement $statement)
		{
			return $statement->getTables();
		}
		
		/**
		 * Returns the corresponding integer value of the Statement class associated
		 * with the integer value passed or throws an Exception.
		 * 
		 * @param	integer	$joinType	The type of the join.
		 * 
		 * @return	integer
		 * @throws	\Exception
		 */				
		private function getJoin($joinType)
		{
			if(isset($this->joins[$joinType]))
			{
				return $this->joins[$joinType];
			}
			throw new \Exception('SQLStatementEvaluator.getJoin(): Unsupported join type ' . $joinType . ' called.');
		}
		
		/**
		 * Evalutes the joins of the provided Statement.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be
		 * 									based on
		 * 
		 * @return	string
		 */
		private function evaluateJoins(Statement $statement)
		{
			$where = '';
						
			foreach($statement->getJoins() as $join)
			{
				$where .= ' ' . $this->getJoin($join[2]) . ' JOIN ' . $join[0]
							. (
								((bool)$join[3])
								? ' USING (' . $join[1] . ')'
								: ' ON ' . $join[1]
							);
			}
			return $where;
		}
				
		/**
		 * Evalutes the where conditions of the provided Statement.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be
		 * 									based on.
		 * 
		 * @return	string
		 */
		private function evaluateWhere(Statement $statement)
		{
			if($statement->hasWhere())
			{
				return ' WHERE ' . $statement->getWhere();
			}
			return '';			
		}
		
		/**
		 * Evaluates the ordering information of the provided Statement.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be
		 * 									based on.
		 * 
		 * @return	string
		 */
		private function evaluateOrdering(Statement $statement)
		{
			if($statement->hasOrdering())
			{
				$order = ' ORDER BY ' . implode(', ', $statement->getOrder());
				return $order;
			}			
		}
		
		/**
		 * Evaluates the limiting information of the provided Statement.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be
		 * 									based on.
		 * 
		 * @return	string
		 */
		private function evaluateLimit(Statement $statement)
		{
			if($statement->hasLimit())
			{
				return ' LIMIT ' . $statement->getLimitOffset() . ',' . $statement->getLimitLength();
			}
			return '';
		}
		
		/**
		 * Evaluates the having conditions of the provided Statement.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be
		 * 									based on.
		 * 
		 * @return	string
		 */
		private function evaluateHaving(Statement $statement)
		{
			if($statement->hasHaving())
			{
				return ' HAVING ' . $statement->getHaving();
			}
			return '';	
		}
		
		/**
		 * Evaluates the group by information of the provided Statement.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be
		 * 									based on.
		 * 
		 * @return	string
		 */
		private function evaluateGroupBy(Statement $statement)
		{
			return $statement->hasGroupBy() ? ' GROUP BY '. implode(',', $statement->getGroupBy()) : '';
		}
		
		/**
		 * Evaluates a select statement.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be
		 * 									based on.
		 * 
		 * @return 	string
		 */
		private function evaluateSelect(Statement $statement)
		{
			$FIELDS = $this->evaluateWhat($statement);
			$TABLES = $this->evaluateTables($statement);
			$TABLES .= $this->evaluateJoins($statement);
			$WHERE = $this->evaluateWhere($statement);
			$GROUPBY = $this->evaluateGroupBy($statement);
			$HAVING = $this->evaluateHaving($statement);
			$ORDERBY = $this->evaluateOrdering($statement);
			$LIMIT = $this->evaluateLimit($statement);
						
			return 			
				'SELECT ' . $FIELDS . 
				' FROM '. $TABLES . 
				$WHERE .
				$GROUPBY .
				$HAVING .
				$ORDERBY .
				$LIMIT 
			;			
		}
		
		/**
		 * Evaluates a show tables statement.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be based on.
		 * @return 	string
		 */
		private function evaluateShowTables(Statement $statement)
		{
			$TABLES = $this->evaluateTables($statement);
			return
				'SHOW FULL TABLES'
					. (($TABLES) ? ' LIKE "' . $TABLES . '"' : '')
					. ';'
				;
		}
		
		/**
		 * Evaluates a show index from table statement.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be based on.
		 * @return 	string
		 */
		private function evaluateShowTableColumns(Statement $statement)
		{
			$TABLES = $this->evaluateTables($statement);
			$WHERE = $this->evaluateWhere($statement);

			return 			
				'SHOW FULL COLUMNS FROM '
				. $TABLES
				. $WHERE;
			;			
		}
		
		/**
		 * Evaluates a show index from table statement.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be based on.
		 * @return 	string
		 */
		private function evaluateShowTableIndexes(Statement $statement)
		{
			$TABLES = $this->evaluateTables($statement);
			$WHERE = $this->evaluateWhere($statement);

			return 			
				'SHOW INDEX FROM '
				. $TABLES
				. $WHERE;
			;			
		}
		
		/**
		 * Evaluates an insert Statement.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be based on.
		 * @return 	string
		 */
		private function evaluateInsert(Statement $statement)
		{			
			$TABLES = $this->evaluateTables($statement);
			$FIELDS = $this->evaluateFields($statement);
			$VALUES = $this->evaluateValues($statement);
			
			return
				'INSERT INTO '. $TABLES .
				' (' . $FIELDS . ') ' .
				'VALUES (' . $VALUES . ')'
			;				
		}
		
		/**
		 * Evaluates an update Statement.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be
		 * 									based on.
		 * 
		 * @return 	string
		 */		
		private function evaluateUpdate(Statement $statement)
		{
			$TABLES = $this->evaluateTables($statement);
			$SETS = $this->evaluateSets($statement);
			$WHERE = $this->evaluateWhere($statement);			
			
			return 
				'UPDATE ' . $TABLES .
				' SET ' . $SETS .
				$WHERE;
		}
		
		/**
		 * Evaluates a delete Statement.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be
		 * 									based on.
		 * 
		 * @return 	string
		 */		
		private function evaluateDelete(Statement $statement)
		{
			$TABLES = $this->evaluateTables($statement);
			$WHERE = $this->evaluateWhere($statement);	
						
			return
				'DELETE FROM '. $TABLES .
				$WHERE;
		}
		
		/**
		 * Evaluates the provided statement based on its type.
		 * 
		 * @param	Statement	$statement	The Statement the evaluation should be
		 * 									based on.
		 * 
		 * @return 	string
		 * @throws	\Exception
		 */ 
		public function evaluate(Statement $statement)
		{
			switch ($statement->getType())
			{
				case Statement::$TYPE_SELECT:					
					return $this->evaluateSelect($statement);
					
				case Statement::$TYPE_INSERT:					
					return $this->evaluateInsert($statement);
					
				case Statement::$TYPE_UPDATE:					
					return $this->evaluateUpdate($statement);
					
				case Statement::$TYPE_DELETE:					
					return $this->evaluateDelete($statement);
																			
				case Statement::$TYPE_SHOW_TABLES:
					return $this->evaluateShowTables($statement);
					
				case Statement::$TYPE_SHOW_TABLE_COLUMNS:
					return $this->evaluateShowTableColumns($statement);

				case Statement::$TYPE_SHOW_TABLE_INDEXES:
					return $this->evaluateShowTableIndexes($statement);
					
				default:
					throw new \Exception('Unrecognized statement type');
			}						
		}
	}
	