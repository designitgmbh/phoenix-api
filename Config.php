<?php

namespace api; 

class config {
	
	private
		$path,
		$params
	;
	
	public function __construct($path = 'config') {
		$this->path = $path;
	}

	public function __call($name, $arguments) {
		return
			include (
				dirname(__FILE__)
				. '/../'
				. $this->path
				. '/'
				. filter_var($name, FILTER_SANITIZE_STRING)
				. '.php'
			);
	}

}