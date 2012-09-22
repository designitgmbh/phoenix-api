<?php

	namespace api\Logging;
	
	use api\PathingAbstraction\Statement;
	use api\PathingAbstraction\SQLStatementEvaluator;						
	
	class PDOLogger extends AbstractLogger implements LoggerInterface
	{
		private
			$database,
			$table
		;
		
		public function __construct(PDOService $database, $table)
		{
			$this->database = $database;
			$this->table = $table;
		}
				
		public function log($data)
		{
			$timestamp = time();
			
			$value = array(
				'pk'	=>	$this->getPk(),
				'data'	=>	$data
			);
			
			$stmt = new Statement;			
			$stmt
				->insertInto($this->table)
				->field('timestamp')->value($timestamp)
				->field('pk')->value(md5($this->getPk()))
				->field('uid')->value($this->getUid())
				->field('model')->value($this->getModel())
				->field('data')->value(json_encode($value));
			;
			
			$this->database->query($stmt);
		}
	}
	