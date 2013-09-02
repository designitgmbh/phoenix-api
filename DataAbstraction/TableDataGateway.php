<?php

	namespace api\DataAbstraction;

	use api\Services\PDOService;
	use api\PathingAbstraction\Statement;
	use api\PathingAbstraction\Condition;
	use api\PathingAbstraction\AndConditionGroup;

	/**
	 * The TableDataGateway class provides a layer of abstraction to the
	 * database. It offers basic CRUD operations on objects.
	 * 
	 * @author 		Daniel Legien <d.legien@design-it.de>
	 * @package		api
	 * @subpackage	DataAbstraction
	 */
	abstract class TableDataGateway implements DataGatewayInterface
	{
		protected
			/**
			 * Cache for the old objects.
			 * 
			 * @var array
			 */
			$oldObjects = array(),
			
			/**
			 * The service used for the connection to the database.
			 * 
			 * @var PDOService
			 */
			$connection	
		;
		
		/**
		 * Stores the old object in the gateway and saves a hash
		 * in the new object. to identify it later.
		 * 
		 * @param	mixed	$obj	The object to store.
		 */
		protected function storeOldObject(&$obj)
		{			
			$oldObj = unserialize(serialize($obj));
			$hash = spl_object_hash($oldObj);
			
			$this->oldObjects[$hash] = $oldObj;
			$obj->objhashentry = $hash;
		}
		
		/**
		 * Returns the keys of an object using the toArray method
		 * of the object.
		 * 
		 * @param	mixed	$obj	The object to check the keys for.
		 * 
		 * @return	array
		 */
		protected function getKeys($obj)
		{
			return array_keys($obj->toArray());
		}
				
		/**
		 * Creates an array of the necessary binds to perform when
		 * manipulating objects for the given object.
		 * 
		 * If the prefix parameter is set the fields are prefixed
		 * with an o. This is necessary when updating an object
		 * and binding old and new values with the same names.
		 * 
		 * @param	mixed	$obj	The object to generate the bind for.
		 * @param	mixed	$prefix	Whether the prefix should be used.
		 */		
		protected function generateBind($obj, $prefix = NULL)
		{
			$bind = array();
			
			foreach($obj->toArray() as $key => $value)
			{
				if(!is_null($prefix))
				{
					$key = "o$key";
				}
				$bind[':'.$key] = $value;
			}			
			return $bind;
		}
		
		/**
		 * Removes the database representation of the given object. Returns
		 * true upon success and throws an Exception if the database
		 * operations fail.
		 * 
		 * @param	mixed	$obj	The object to delete.
		 * 
		 * @return	bool
		 * @throws	\Exception
		 */
		public function delete($obj)
		{
			$objKeys = $this->getKeys($obj);
			
			$stmt = new Statement;
			$stmt->deleteFrom($this->table);
						
			$grp = new AndConditionGroup;
			foreach($objKeys as $objKeys)
			{
				$grp->set(new Condition($objKeys, '=', ':o'.$objKeys, false));					
			}
			$stmt->where($grp);

			if(!$statement = $this->connection->prepare($stmt))
			{
				throw new \Exception('Couldn\'t prepare statement in TableDataGateway.delete');
			}
			
			if(!$statement->execute($this->generateBind($obj, 'o')))
			{
				throw new \Exception('Couldn\'t execute statement in TableDataGateway.delete with ' . implode(', ', $this->generateBind($obj, 'o')));
			}
						
			return TRUE;
		}
		
		/**
		 * Creates a new database representation for the given object. Returns
		 * true upon success or throws an Exception if the database
		 * actions don't succeed.
		 * 
		 * 
		 * @param	mixed	$obj	The object to create.
		 * 
		 * @return	bool
		 * @throws	\Exception
		 */
		public function create($obj)
		{
			$objKeys = $this->getKeys($obj);
				
			$stmt = new Statement;
			$stmt
				->insertInto($this->table)
			;	
			
			foreach($objKeys as $objKey)
			{
				$stmt->field($objKey);
				$stmt->value(':'.$objKey, FALSE);
			}
															
			if(!$statement = $this->connection->prepare($stmt))
			{
				throw new \Exception('Couldn\'t prepare statement in TableDataGateway.create');
			}
						
			if(!$statement->execute($this->generateBind($obj)))
			{
				$this->connection->catchError($stmt);
				throw new \Exception('TableDataGateway.create: Couldn\'t execute statement '.$stmt.' with ' . implode(', ',$this->generateBind($obj)));
			}
			
			return $this->connection->lastInsertId();
		}
		
		/**
		 * Updates the database representation of the provided object. Requires
		 * the object to be retrieved using the find method first. Returns true
		 * when succeeding.
		 * 
		 * Returns false if there is no existing hash entry for the provided
		 * project.
		 * 
		 * @param	mixed	$obj	The object to update.
		 * 
		 * @return 	bool
		 * @throws	\Exception
		 */
		public function update($obj)
		{
			if(isset($this->oldObjects[$obj->objhashentry]) && $obj instanceof $this->oldObjects[$obj->objhashentry])
			{
				$oldObj = $this->oldObjects[$obj->objhashentry];				
				
				$newObjKeys = $this->getKeys($obj);
				$oldObjKeys = $this->getKeys($oldObj);
				
				$stmt = new Statement;
				
				$stmt
					->update($this->table);
					
				foreach($newObjKeys as $newObjKey)
				{
					$stmt->set($newObjKey, ':'.$newObjKey, FALSE);	
				}
				
				$grp = new AndConditionGroup;
				foreach($oldObjKeys as $oldObjKey)
				{
					$grp->set(new Condition($oldObjKey, '=', ':o'.$oldObjKey, FALSE));					
				}
				$stmt->where($grp);
				
				if(!$statement = $this->connection->prepare($stmt))
				{
					throw new \Exception('Couldn\'t prepare statement in TableDataGateway.update');
				}
				
				$binds = array_merge($this->generateBind($obj),$this->generateBind($oldObj, 'o'));
						
				if(!$statement->execute($binds))
				{
					throw new \Exception('Couldn\'t execute statement in TableDataGateway.update with ' . implode(', ',$binds));
				}
				
				return TRUE;				
			}
			return FALSE;
		}
		
		/**
		 * Returns an associative array of results from the database matching the specified
		 * filters and ordering.
		 * 
		 * @param	array	$filters	The filters to apply.
		 * @param	array	$orderBy	The ordering to apply.
		 * @aram	bool	$set		Whether to return an array of objects or an object.
		 * 
		 * @return	mixed
		 * @throws	\Exception
		 */
		protected function find(array $filters = array(), array $orderBy = array(), $set = FALSE)
		{
			$stmt = new Statement;
			$stmt
				->select('*')
				->from($this->table)
			;
						
			$bind = array();
			
			if(count($filters) > 0)
			{
				$grp = new AndConditionGroup; 
			
				foreach($filters as $filter)
				{
					if($filter instanceof TableDataGatewayFilter)
					{
						$grp->set(new Condition($filter->getField(), $filter->getRelation(), ':'.$filter->getField(), FALSE));					
						$bind[':'.$filter->getField()] = $filter->getValue();						
					}
					else
					{
						throw new \Exception('TableDataGateway find requires the provided filter list to consist of TableDataGatewayFilters');
					}					
				}
				$stmt->where($grp);
			}
							
			foreach($orderBy as $order)
			{
				if($order instanceof TableDataGatewayOrdering)
				{
					$stmt->orderby($order->getField(), $order->getDirection());									
				}
				else
				{
					throw new \Exception('TableDataGateway find requires the provided ordering list to consist of TableDataGatewayOrderings');
				}
			}
						
			if(!$statement = $this->connection->prepare($stmt))
			{
				throw new \Exception('Couldn\'t prepare statement.');
			}
						
			if(count($bind) > 0)
			{
				if(!$statement->execute($bind))
				{
					try{
						$this->connection->catchError($stmt);
					}
					catch(\Exception $e)
					{
						throw new \Exception('TableDataGateway.create: ' . $e->getMessage() . '. Couldn\'t execute statement '.$stmt.' with ' . implode(', ',$bind));
					}					
				}
			}
			else
			{
				if(!$statement->execute())
				{
					try{
						$this->connection->catchError($stmt);
					}
					catch(\Exception $e)
					{
						throw new \Exception('TableDataGateway.create: ' . $e->getMessage() . '. Couldn\'t execute statement '.$stmt);	
					}				
				}	
			}
			
			if($set)
			{
				$objects = $statement->fetchAll(\PDO::FETCH_CLASS, $this->modelName);
				foreach($objects as $newobject)
				{
					$this->storeOldObject($newobject);
				}
				
				return $objects;
			}
			else
			{
				$newobject = $statement->fetchObject($this->modelName);
				if ($newobject !== false)
					$this->storeOldObject($newobject);
				return $newobject;
			}
		}

		/**
		 * Alias for the find function. Returns an array of objects instead
		 * of a single object.
		 * 
		 * @param	array	$filters	The filters to apply.
		 * @param	array	$orderBy	The ordering to apply.
		 * 
		 * @return	array
		 * @throws	\Exception	
		 */
		public function findAll(array $filters = array(), array $orderBy = array())
		{
			return $this->find($filters, $orderBy, TRUE);
		}

		/**
		 * A custom find function that takes a Statement as a parameter and
		 * executes it, returning an array of objects.
		 * 
		 * @param	Statement	$stmt	The statement to execute
		 * 
		 * @return	array
		 * @throws	Exception
		 */
		public function findCustom(Statement $stmt)
		{							
			if(!$statement = $this->connection->prepare($stmt))
			{
				throw new \Exception('TableDataGateway.findCustom(): Couldn\'t prepare statement');
			}
						
			$statement->execute();	

			return $statement->fetchAll(\PDO::FETCH_CLASS, $this->modelName);
		}
	}
