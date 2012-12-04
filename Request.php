<?php

namespace api;

/**
 *
 *
 * @author Daniel Legien <d.legien@design-it.de>
 */
class Request {
	private $request;

	public function __construct($request) {
		$this->request = $request;
	}

	public function getString($name, $escape = TRUE) {
		return $escape ? mysql_escape_string($this->getValue($name)) : $this->getValue($name);
	}

	public function getInt($name) {
		if ($this->isInt($name)) {
			return (int)$this->getValue($name);
		}
		throw new \InvalidArgumentException('Request.getInt: Der übergebene Wert ist nicht numerisch.');
	}

	public function getArray($name) {
		return (array)$this->getValue($name);
	}

	private function getValue($name) {
		if($this->hasEntry($name)) {
			return $this->request[$name];
		}
		throw new \InvalidArgumentException('Request.getValue(): Es gibt keinen Eintrag mit dem Namen ' . $name . '.');
	}
	
	public function isInt($name) {
		return ($this->hasEntry($name) && is_numeric($this->getValue($name)));
	}

	public function hasEntry($name) {
		return isset($this->request[$name]);
	}

	public function entryEquals($name, $value) {
		return isset($this->request[$name]) && $this->request[$name] === $value;
	}

}
