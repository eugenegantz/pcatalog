<?php

Namespace Base;


Class DBMySQL extends DB {

	public $dbtype = "mysql";
	
	private $_dbObject;
	
	private $_dbaddress;
	
	private $_dbname;

	public function init($A = Array()){
	
		try {
			if (
				!isset(
					$A["dblogin"],
					$A["dbpassword"],
					$A["dbname"],
					$A["dbaddress"]
				) 
			){
				throw new \Exception('Not anough DB arguments');
			}
		} catch ( Exception $e ) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			return;
		}

		$dblogin = $A["dblogin"];
		$dbpwd = $A["dbpassword"];
		$dbname = $A["dbname"];
		$dbaddress = $this->_dbaddress =  $A["dbaddress"];
		
		// Если объект БД уже указан в качестве аргумента.
		// TODO обрабатывать ошибку если не удалось подключиться к БД
		if (
			isset($A["dbobject"]) 
			&& gettype($A["dbobject"]) == "object" 
		){
			$this->_dbObject = $A["dbobject"];
		} else {
			$this->_dbObject = new \mysqli($dbaddress, $dblogin, $dbpwd, $dbname);

			$this->_dbObject->set_charset("utf8");
			
			if ($this->_dbObject->connect_error) {
				echo 'Connect Error (' . $this->_dbObject->connect_errno . ') ' . $this->_dbObject->connect_error;
			}
		}
	}
	
	public function term (){
		if (method_exists($this->_dbObject, "close")){
			$this->_dbObject->close();
		}
	}
	
	public function query($A = Array()){
		if ( !isset($A['query']) || !trim($A['query']) ) return;
	
		$query = $A['query'];
		
		$return = Array(
			"err" => Array(),
			"num_rows" => 0,
			"rows" => Array()
		);


		
		if (  $dbres = $this->_dbObject->query($query)  ) {

			if ( $dbres === true ){

				// Успешно вып. запрос

			} elseif ( get_class($dbres) == "mysqli_result" ) {
				while( $dbrow = $dbres->fetch_array(MYSQLI_ASSOC) ){
					array_push($return["rows"], $dbrow);
				}
				$return["num_rows"] = $dbres->num_rows;
				$dbres->close();
			}

		} else {
		
			foreach($this->_dbObject->error_list as $err){
				array_push($return['err'], $err["error"]);
			}
			
		}
		
		// -------------------- Logs --------------------
		
		$logstr = Array(
			Date("y-m-d H:i:s"),
			'addr: ' . $this->_dbaddress,
			'err: ' . implode(" ",$return['err']),
			'query: ' . $query
		);

		$this->addLog(implode(" / ",$logstr));
		
		// -------------------- return --------------------
		
		return $return;
	}
	
}