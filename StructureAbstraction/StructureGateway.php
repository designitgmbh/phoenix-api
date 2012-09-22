<?php

	namespace api\StructureAbstraction;

	use api\Services\PDOService;
	use api\PathingAbstraction\Statement;
	use api\PathingAbstraction\Condition;
	use api\PathingAbstraction\SQLStatementEvaluator;
		
	/**
	 * 
	 * @author 		Rüdiger Willmann <r.willmann@design-it.de>
	 * @package		api
	 * @subpackage	StructureAbstraction
	 */
	class StructureGateway implements StructureGatewayInterface
	{
		protected
			/**
			 * The service used for the connection to the database.
			 * 
			 * @var PDOService
			 */
			$connection,
			
			/**
			 * The builder used to build the object instances.
			 * 
			 * @var	ObjectBuilder
			 */
			$builder		
		;

		/**
		 * Creates a new StructureGateway with the provided service.
		 * 
		 * @param	PDOService		$connection	The service to use.
		 * @return	void
		 */
		public function __construct(PDOService $connection)
		{
			$this->connection = $connection;
		}
		
		/**
		 * Get a List of all Tables
		 * 
		 * @return [StructureGatewayTable]
		 */
		public function findTables($pattern = FALSE) {
			$statement = new Statement;
			$statement->showTables($pattern);
			
			return $this->connection->query($statement)->fetchAll(
				\PDO::FETCH_CLASS,
				'api\StructureAbstraction\StructureGatewayTable'
			);
		}

		/**
		 * Get table columns 
		 * 
		 * @param StructureGatewayTable $table
		 * @param string $pattern
		 * @return StructureGatewayTableColumn
		 */		
		public function findTableColumns(StructureGatewayTable $table, $pattern=false) {
			$statement = new Statement;
			$statement->showTableColumns($table->getName());
			if ($pattern !== false) {
				$statement->where(new Condition('Field', 'like', $pattern));
			}

			return $this->connection->query($statement)->fetchAll(
				\PDO::FETCH_CLASS,
				'api\StructureAbstraction\StructureGatewayTable\Column'
			);
		}
		
		/**
		 * Get table Indexes
		 * 
		 * @param StructureGatewayTable $table
		 * @return [StructureGatewayTable\Index]
		 */
		public function findTableIndexes(StructureGatewayTable $table) {				
			$statement = new Statement;
			$statement->showTableIndexes($table->getName());

			$indexes = array_unique(
				$this->connection->query($statement)->fetchAll(
					\PDO::FETCH_CLASS,
					'api\StructureAbstraction\StructureGatewayTable\Index'
				)
			);
			
			foreach($indexes as $key => $index) {			
				$indexes[$key]->setIndexColumns(
					$this->findTableIndexColumns($table, $index)
				);
			}
			
			return $indexes;			
		}	
		
		/**
		 * Get colums of a table index
		 * 
		 * @param StructureGatewayTable $table
		 * @param StructureGatewayTable\Index $table
		 * @return [StructureGatewayTable\Column]
		 */
		public function findTableIndexColumns(StructureGatewayTable $table, StructureGatewayTable\Index $index) {				
			$statement = new Statement;
			$statement->showTableIndexes($table->getName());
			$statement->where(new Condition('Key_name', '=', $index->getName()));
			
			$columns = array();
			
			foreach($this->connection->query($statement)->fetchAll(
				\PDO::FETCH_CLASS,
				'api\StructureAbstraction\StructureGatewayTable\IndexColumn'
			) as $indexColumn) {
				$columns[$indexColumn->getName()] = reset($this->findTableColumns($table, $indexColumn->getName()));
			}
						
			return $columns;
		}	
	}