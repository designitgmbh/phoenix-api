<?php

namespace api;

use generated\gateway\bahn\api\RunlockTableDataGateway as api;
use generated\model\bahn\api\Runlock as model;

class Runlock {
	
	private
		$gateway,
		$model,
		$hasLock
	;
	
	public function getLock() {
		if ($this->hasLock() == true) {
			return $this->hasLock();
		}
		
		try {
			$this->gateway->create($this->model);
			$this->setLock(true);
			return $this->hasLock();
			
		} catch (\Exception $e) {
			$this->setLock(false);
			return $this->hasLock();
		}		
	}
	
	private function setLock($flag) {
		$this->hasLock = $flag;
		return $this->hasLock;
	}
	
	public function hasLock() {
		return $this->hasLock;
	}
	
	public function releaseLock() {
		$del = $this->gateway->delete($this->model);
		$this->setLock(!$del);		
		return $del; 
	}

	public function __construct( $gateway, $Runlock_id ) {
		$this->gateway = $gateway;
		$this->model = new model(array(
			'Runlock_id' => md5(trim($Runlock_id)),
			'Instance_id' => uniqid()
		));
		$this->hasLock = false;
	}

	public function __destruct() {
		$this->releaseLock();
	}

}
