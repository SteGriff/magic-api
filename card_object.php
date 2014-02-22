<?php
class card
{
	//An associative array representing a single card
	// The keys are card properties such as flavor_text
	private $c;
	
	//Initialise to db object (or just pass in null)
	function __construct($fetchedArray) {
		$this->c = null;
		if (isset($fetchedArray)){
			foreach ($fetchedArray as $key => $value){
				if ($value){
					$this->set($key, $value);
				}
			}
		}
		$this->clear_error();
	}
	
	function set($property, $value){
		$this->c[$property] = $value;
	}
	function get($property){
		return isset($this->c[$property]) ? $this->c[$property] : null;
	}
	function json(){
		//Make a copy so we can remove some stuff
		// (Arrays are assigned by value, not ref, in php)
		$j = $this->c;
		unset($j['ID']);
		return json_encode($j);
	}
	
	function set_error($e){
		if ($this->no_error()){
			$this->c['error'] = $e;
		}	
	}
	function clear_error(){
		unset($this->c['error']);
	}
	function no_error(){
		return empty($this->c['error']);
	}
		
}
?>