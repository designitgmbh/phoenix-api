<?php

	namespace api\Services;
		
	use api\PathingAbstraction\StatementEvaluator;	
	use api\PathingAbstraction\Statement;
	use api\DataAbstraction\DataGatewayInterface;
		
	/**
	 * The PDOService provides access to the database using the
	 * PDO class.
	 * 
	 * @author Daniel Legien <d.legien@design-it.de>
	 */
	class PDOService implements ServiceInterface, DataGatewayInterface
	{
		private
			/**
			 * The established connection using the PHP built in PDO class.
			 * 
			 * @var	\PDO
			 */
			$connection,
			/**
			 * The evaluator used to evaluate the provided database pathing
			 * statements.
			 * 
			 * @var StatementEvaluator
			 */
			$evaluator;
		
		/**
		 * Creates a new PDOService that uses the given evaluator to evalute
		 * Statements passed to it. It establishes the database connection
		 * based on the provided type, host, username and password information.
		 * 
		 * @param	StatementEvaluator	$evaluator	The evaluator for the Statement.
		 * @param	string				$type		The type of the database.
		 * @param	string				$database	The name of the database.
		 * @param	string				$host		The name of the host.
		 * @param	string				$username	The username for the database user.
		 * @param	string				$password	The password for the database user.
		 */
		public function __construct(StatementEvaluator $evaluator, $type, $database, $host = NULL, $username = NULL, $password = NULL)
		{
			if($type == 'sqlite')
			{
				// We are using sqlite
				$dsn = $type.':'.$database;	
			}
			elseif($type == 'mysql')
			{
				// We are using mysql
				$dsn = $type.':dbname='.$database.';host='.$host;
			}
						
			$this->connection = new \PDO($dsn, $username, $password);
			$this->evaluator = $evaluator;
		}
		
		/**
		 * Prepares a given statement by evaluating it and passing it to the
		 * PDO instances associated with the service.
		 * 
		 * @param	Statement	$statement	The statement that should be prepared.
		 * 
		 * @return	\PDOStatement
		 */
		public function prepare(Statement $statement)
		{
			$pdoStatement = $this->connection->prepare($this->evaluator->evaluate($statement));
			if($pdoStatement)
			{
				return $pdoStatement;
			}
			$this->catchError($statement);				
		}
		
		/**
		 * Performs a query using the evaluator to retrieve the actual Statement.
		 * 
		 * @param	Statement	$statement	The statement that the query should be
		 * 									performed with.
		 * 
		 * @return \PDOStatement
		 * @throws \Exception
		 */
		public function query(Statement $statement)
		{	
			$pdoStatement = $this->connection->query($this->evaluator->evaluate($statement));
			if($pdoStatement)
			{
				return $pdoStatement;
			}
			$this->catchError($statement);
		}
		
		/**
		 * Catches errors thrown by the PDO class and throws an Exception
		 * describing the error.
		 * 
		 * @param	Statement	$statement	The Statement performed when the error occured.
		 * 
		 * @throws	\Exception
		 */
		public function catchError(Statement $statement)
		{
			$error = $this->connection->errorInfo();
						
			throw new \Exception('Database error: "' . $error[2]. '" during ' . $this->evaluator->evaluate($statement));
		}
		
		public function lastInsertId()
		{
			return $this->connection->lastInsertId();	
		}
		
		/**
		 * Binds the given value to the given name and the makes sure it's
		 * of the provided value.
		 * 
		 * @param	string	$name	The name of the parameter that should be bound.
		 * @param	mxied	$value	The value of the parameter that should be bound.
		 * @param	integer	$type	The type of the parameter that should be bound.
		 * 
		 * @return \PDOStatement
		 */
		public function bindValue($name, $value, $type)
		{
			return $this->connection->bindValue($name, $value, $type);
		}	
	}
	