<?php

	namespace api\PathingAbstraction;

	/**
	 * Represents a statement that can be interpreted by a StatementEvaluator.
	 * 
	 * @author 		Daniel Legien <d.legien@design-it.de>
	 * @package		api
	 * @subpackage	PathingAbstraction
	 */
	class Statement
	{
		private
			/**
			 * The type of the query which can have any of the values defined
			 * in this class. (example: Statement::$TYPE_SELECT for select
			 * statements.
			 * 
			 * @var integer
			 */
			$type = NULL,
			
			/**
			 * An array of the fields used in the statements. These are necessary
			 * for selects and inserts.
			 * 
			 * @var array
			 */
			$fields = array(),
			
			/**
			 * The base table used for the statement. This can be any string that
			 * makes sense in the context (example: table name).
			 * 
			 * @var string
			 */
			$tables = NULL,
			
			/**
			 * An array of all the joins that are being performed in the statement.
			 * 
			 * @var array
			 */
			$joins = array(),
			
			/**
			 * The root element of the Condition structure for this statement. This
			 * can be either a Condition or ConditionGroup.
			 */
			$where = NULL,
			
			/**
			 * An array with all the ordering rules for this statement.
			 * 
			 * @var array
			 */
			$order = array(),
			
			/**
			 * An array with all the values that are being assigned in this statement.
			 * These are used for the insert statements.
			 * 
			 * @var array
			 */
			$values = array(),
			
			/**
			 * Flags the Statement as too complicated to be handled.
			 * 
			 * @var bool
			 */
			$tooComplicated = FALSE,
			
			/**
			 * Flags the Statement as using the DISTINCT keyword.
			 * 
			 * @var bool
			 */
			$hasDistinct = FALSE,
			
			/**
			 * An array with the limit information such as offset and length,
			 * otherwise NULL.
			 * 
			 * @var array
			 */
			$limit = NULL,
			
			/**
			 * The root element of the Condition structure for this statement. This
			 * can be either a Condition or ConditionGroup.
			 */
			$havings = NULL,
			
			/**
			 * The sets associated with this Statement. These are used for update
			 * Statements.
			 * 
			 * @var array
			 */
			$sets = array(),
			
			/**
			 * The information for the group by part of this statement.
			 * 
			 * @var array
			 */
			$groupby = array()
		;
		
		static
			/**
			 * Constant for LEFT JOINs.
			 * 
			 * @var integer
			 */
			$LEFT_JOIN = 0,
			
			/**
			 * Constant for RIGHT JOINs.
			 * 
			 * @var integer
			 */
			$RIGHT_JOIN = 1,
			
			/**
			 * Constant for CROSS JOINs.
			 * 
			 * @var integer
			 */			
			$CROSS_JOIN = 2,
			
			/**
			 * Constant for SELECT Statments.
			 * 
			 * @var integer
			 */
			$TYPE_SELECT = 0,

			/**
			 * Constant for INSERT Statments.
			 * 
			 * @var integer
			 */			
			$TYPE_INSERT = 1,
			
			/**
			 * Constant for UPDATE Statments.
			 * 
			 * @var integer
			 */			
			$TYPE_UPDATE = 2,
			
			/**
			 * Constant for DELETE Statments.
			 * 
			 * @var integer
			 */			
			$TYPE_DELETE = 3;
		
		/**
		 * Creates a new Statement object.
		 */
		public function __construct()
		{			
		}
		
		/**
		 * Returns whether or not the Statement is flagged using the DISTINCT
		 * keyword.
		 * 
		 * @return bool
		 */
		public function hasDistinct()
		{
			return $this->hasDistinct;
		}
		
		/**
		 * Sets a new field to be retrieved. An optional alias can be provided
		 * to be used for the field.
		 * 
		 * @param	string	$what	The field that should be selected.
		 * @param	string	$alias	The alias to be used for the field.
		 * 
		 * @return Statement
		 */
		public function select($what, $alias = NULL)
		{
			$this->setType(self::$TYPE_SELECT);			
			if(is_null($alias))
			{
				$alias = $what;
			}
			
			if(stripos($what,'distinct'))
			{
				$this->hasDistinct = TRUE;
			}
			
			$this->fields[$alias] = $what;
			return $this;
		}
		
		/**
		 * Adds a root element for the having part of the Statement. If used
		 * twice it will overwrite the old value. If the provided condition is
		 * neither a Condition nor a ConditionGroup an Exception is thrown.
		 * 
		 * @param	mixed	$condition	The root element for the having clauses of this statement.
		 * 
		 * @return	Statement
		 * @throws	\Exception
		 */
		public function having($condition)
		{
			if($condition instanceof Condition || $condition instanceof ConditionGroup)
			{
				$this->havings = $condition; 
				return $this;				
			}
			throw new \Exception('Can only add Conditions or ConditionGroups to a statement\'s where block');
		}
		
		/**
		 * Sets the limit information for the Statement to the provided
		 * offset and length. If non integer values are provieded an Exception
		 * is thrown.
		 * 
		 * @param	integer	$offset	The offset for the limit operation.
		 * @param	integer	$length	The length for the limit operation.
		 * 
		 * @return 	Statement
		 * @throws	\Exception
		 */
		public function limit($offset, $length)
		{
			if(!is_int($offset) || !is_int($length))
			{
				throw new \Exception('Invalid limit parameters.');
			}
			$this->limit = array($offset, $length);
			return $this;
		}
		
		/**
		 * Adds another statement to connect to this statement using the
		 * UNION keyword.
		 * 
		 * @param	Statement	$statement	The statement that the union should be made with.
		 * 
		 * @return 	Statement
		 */
		public function union(Statement $statement)
		{
			$this->unions[] = $statement;
			return $this;
		}
		
		/**
		 * Returns whether or not the Statement is too complicated to be
		 * handled further.
		 * 
		 * @return bool
		 */
		public function isTooComplicated()
		{
			return $this->tooComplicated;
		}
		
		/**
		 * Returns whether or not the Statement has ordering information
		 * stored or not.
		 * 
		 * @return bool
		 */
		public function hasOrdering()
		{
			return count($this->order) > 0;			
		}
		
		/**
		 * Returns whether or not the Statement has a root element for the
		 * having Conditions.
		 * 
		 * @return bool
		 */
		public function hasHaving()
		{
			return !is_null($this->havings);
		}
		
		/**
		 * Returns the root element of the Statement's having Conditions or
		 * NULL when no information exists.
		 */
		public function getHaving()
		{
			return $this->havings;
		}
		
		/**
		 * Flags the Statement as too complicated to be handled further.
		 * 
		 * @return Statement 
		 */
		public function setTooComplicated()
		{
			$this->tooComplicated = TRUE;
			return $this;
		}
		
		/**
		 * Returns whether or not there is a limit defined for this Statement.
		 * 
		 * @return bool
		 */
		public function hasLimit()
		{
			return is_array($this->limit);
		}
		
		/**
		 * Returns the offset defined for the limit of this Statement or throws
		 * an Exception if there is no limit defined.
		 * 
		 * @return	integer
		 * @throws	\Exception
		 */
		public function getLimitOffset()
		{
			if($this->hasLimit())
			{
				return $this->limit[0];
			}
			throw new \Exception('Retrieving non-existant limit offset');
		}
		
		/**
		 * Returns the length defined for the limit of this Statement or throws
		 * an Exception if there is no limit defined.
		 * 
		 * @return	integer
		 * @throws	\Exception
		 */
		public function getLimitLength()
		{
			if($this->hasLimit())
			{
				return $this->limit[1];
			}
			throw new \Exception('Retrieving non-existant limit length');
		}		
		
		/**
		 * Sets the table the following fields and values should be inserted
		 * into. If the method is called twice an Exception is thrown.
		 * 
		 * @param	string	$where	The table used for the insert statement.
		 * 
		 * @return	Statement
		 * @throws	\Exception
		 */
		public function insertInto($where)
		{
			$this->setType(self::$TYPE_INSERT);			
			if(is_null($this->tables))
			{
				$this->tables = $where;
				return $this;
			}
			throw new \Exception('Statement.insertInto(): You can only call this method once on a statement.');
		}
		
		/**
		 * Sets the table for the following sets that should be performed.
		 * If the method is called twice an Exception is thrown
		 * 
		 * @param	string	$where	The table used for the update statement. 
		 * 
		 * @return	Statement
		 * @throws	\Exception
		 */
		public function update($where)
		{
			$this->setType(self::$TYPE_UPDATE);			
			if(is_null($this->tables))
			{
				$this->tables = $where;
				return $this;				
			}
			throw new \Exception('Statement.update(): You can only call this method once on a statement.');
		}
		
		/**
		 * Adds as setting operation for the given field and gives it the
		 * provided value. If the optional parameter $quote is omitted the
		 * value is quoted, otherwise it's not.
		 * 
		 * @param	string		$field 	The field for the setting operation.
		 * @param	mixed		$value	The value the field should be set to.
		 * @param	bool		$quote	Whether the value should be quoted.
		 * 
		 * @return	Statement	
		 */
		public function set($field, $value, $quote = TRUE)
		{
			$this->sets[$field] = $quote ? "'$value'" : $value;
			return $this;
		}
		
		/**
		 * Sets a field that should be inserted into in an insert statement.
		 * 
		 * @param	string	$what	The field the value should be inserted into
		 * 
		 * @return	Statement
		 */
		public function field($what)
		{
			$this->fields[] = $what;
			return $this;
		}
		
		/**
		 * Sets a value that should be added to a field. The order in which
		 * these are called is important. Adviced is using a field statement
		 * directly followed by a value statement.
		 * 
		 * @param	mixed	$value			The value the field should be set to.
		 * @param	bool	$quoteValue		Whether the value should be quoted.
		 * 
		 * @return	Statement
		 */
		public function value($value, $quoteValue = TRUE)
		{
			$this->values[] = $quoteValue ? "'$value'" : $value;
			return $this;
		}
		
		/**
		 * Sets the table to be used for the delete Statement. if this method
		 * is called twice an Exception is thrown.
		 * 
		 * @param	string	$where	The table for the delete statement
		 * 
		 * @return 	Statement 
		 * @throws	\Exception
		 */
		public function deleteFrom($where)
		{
			$this->setType(self::$TYPE_DELETE);			
			if(is_null($this->tables))
			{
				$this->tables = $where;
				return $this;
			}
			throw new \Exception('Statement.deleteFrom(): This method can only be called once for a Statement.');
		}
		
		/**
		 * Sets the type of a Statement to the specified type. If the type is
		 * already set or the type is not a valid integer parameter an exception
		 * is thrown.
		 * 
		 * Valid parameters can be taken from the constants defined in this class.
		 * 
		 * @param	integer	$type	The type of the statement.
		 * 
		 * @throws	\Exception
		 */
		private function setType($type)
		{
			if(is_null($this->type) && is_int($type))
			{
				$this->type = $type;
			}
			elseif(!is_int($type))
			{
				throw new \Exception('Statement.setType(): This method only accepts integer values. ' . $type . ' given.');	
			}
		}
		
		/**
		 * Specifies from what table the data should be retrieved in a select
		 * Statement.
		 * 
		 * @param	string	$where	The table the data should be retrieved from.
		 * 
		 * @return	Statement
		 * @throws	\Exception
		 */
		public function from($where)
		{
			if(is_null($this->tables))
			{
				$this->tables = $where;
				return $this;
			}
			throw new \Exception('Statement.from(): This method can only be called once for a Statement.');
		}
		
		/**
		 * Specifies which additional locations have to be joined. The type parameter
		 * needs to be a valid integer and should be taken from the constants defined in
		 * this class.
		 * 
		 * @param	string	$what	The location that should be joined.
		 * @param	string	$on		The condition under which the location should be joined.
		 * @param	integer	$type	The type of the joining operation.
		 * 
		 * @return 	Statement
		 * @throws	\Exception
		 */
		public function join($what, $on, $type = 1)
		{
			if(is_int($type))
			{
				$this->joins[] = array($what, $on, $type);
				return $this;
			}
			throw new \Exception('Statement.join(): Invalid parameter type given.');
		}
		
		/**
		 * Specifies the where conditions by providing a root element for the condition
		 * tree. The provided condition has to be a Condition or ConditionGroup otherwise
		 * an Exception is thrown.
		 * 
		 * @param	mixed	$condition	The root element for the Statement's where clause.
		 * 
		 * @return	Statement
		 * @throws	\Exception
		 */
		public function where($condition)
		{
			if($condition instanceof Condition || $condition instanceof ConditionGroup)
			{
				$this->where = $condition; 
				return $this;				
			}
			throw new \Exception('Can only add Conditions or ConditionGroups to a statement\'s where block');
		}
		
		/**
		 * Assigns an ordering information to the Statement. If the direction parameter
		 * is omitted the direction ASCending is assumed.
		 * 
		 * @param	string	$field		The field that the result should be ordered by.
		 * @param	string	$direction	The direction the results should be sorted in.
		 * 
		 * @return	Statement
		 */
		public function orderby($field, $direction = 'ASC')
		{
			$this->order[] = "$field $direction";
			return $this;
		}
		
		/**
		 * Returns the fields for the Statement.
		 * 
		 * @return	array
		 */
		public function getFields()
		{
			return $this->fields;
		}
		
		/**
		 * Returns the tables for the Statement or throws an Exception if
		 * none is defined.
		 * 
		 * @return	string
		 * @throws	\Exception
		 */
		public function getTables()
		{
			if(is_null($this->tables))
			{
				throw new \Exception('No source defined');
			}
			return $this->tables;
		}
		
		/**
		 * Returns the Statement's joins.
		 * 
		 * @return	array
		 */
		public function getJoins()
		{
			return $this->joins;
		}
		
		/**
		 * Returns the string representation of the associated where
		 * Condition.
		 * 
		 * @return	string
		 */
		public function getWhere()
		{
			return $this->hasWhere() ? (string)$this->where : '';
		}
		
		/**
		 * Returns whether or not the Statement has a where condition defined.
		 * 
		 * @return	bool
		 */
		public function hasWhere()
		{
			return !is_null($this->where);
		}
		
		/**
		 * Returns the Statement's order information.
		 * 
		 * @return	array
		 */
		public function getOrder()
		{
			return $this->order;
		}
		
		/**
		 * Returns the Statement's sets.
		 * 
		 * @return	array
		 */
		public function getSets()
		{
			return $this->sets;
		}

		/**
		 * Returns the Statement's type.
		 * 
		 * @return	integer
		 */		
		public function getType()
		{
			return $this->type;
		}
		
		/**
		 * Returns the Statement's values.
		 * 
		 * @return	array
		 */
		public function getValues()
		{
			return $this->values;
		}
		
		/**
		 * Creates a deep copy of the Statement.
		 * 
		 * @return	Statement
		 */
		public function copy()
		{
			return unserialize(serialize($this)); 
		}
		
		/**
		 * Returns the group by information of this statement.
		 * 
		 * @return	array
		 */
		public function getGroupBy()
		{
			return $this->groupby;
		}
		
		/**
		 * Returns whether there are group by informations saved in this
		 * statement.
		 */
		public function hasGroupBy()
		{
			return (count($this->groupby) > 0);
		}
		
		/**
		 * Identifies the Statement.
		 * 
		 * @return	string
		 */
		public function __toString()
		{
			return 'Statement string representation';
		}
		
		/**
		 * Adds a field to the group by information of this statement.
		 * 
		 * @param	string	$field	The field the grouping should work with
		 * 
		 * @return	Statement
		 */
		public function groupby($field)
		{
			$this->groupby[] = $field;
			return $this;
		}
		
	}
