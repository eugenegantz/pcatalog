<?php

Namespace Base;

abstract Class DB {

	static $instances = Array();
	
	static $log = null;
	
	function __construct($A = Array()){
		$logfile = (isset($A['logfile']) ? $A['logfile'] : null );

		array_push(self::$instances, $this);
		
		self::$log = new DBLog(Array(
			"logfile" => $logfile
		));
		
		if ( method_exists($this, "init") ){
			$this->init($A); 
		}
	}
	
	function __destruct(){
		if ( method_exists($this, "term") ){
			$this->term(); 
		}
	}
	
	public function getInstances(){
		return self::$instances;
	}
	
	public function addLog($str = ""){
		self::$log->add($str);
	}
	
	abstract public function query();
	
	abstract function init();

	abstract function term();
	
}