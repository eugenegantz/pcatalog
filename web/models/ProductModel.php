<?php

class ProductModel {

	private $id;

	private $name;

	private $price;

	private $regdate;

	private $editdate;

	private $description;

	private $amount;

	private $measure;

	private $status;

	private $attachment = Array();

	private $category = Array();

	public $db;

	public $lastError = null;

	function __construct($arg = Array()){

		if ( isset($arg["db"]) ){

			$this->db = $arg["db"];

		} else {

			$config = \Base\Core::getGlobal("config");

			$this->db = \Base\Core::getGlobal(
				"db",
				Array(
					"dbtype" => "mysql",
					"dbaddress" => $config["db_address"],
					"dblogin" => $config["db_user"],
					"dbpassword" => $config["db_password"],
					"dbname" => $config["db_name"],
					"logfile" => $config["db_logfile"]
				)
			);

			$this->db = $this->db[0];

		}

		$this->utils = new Utils();

	}


	function M1(){
		return 200;
	}


	private $P1 = 300;

	public function setAttachment($value){
		if(!isset($value)) return null;

		if(is_string($value)){
			if ( !$value ) return null;

			$value = \Base\Utils::m_explode([";",","],$value);
			foreach($value as &$val){
				$val = intval($val);
			}
			$this->attachment = $value;
			return true;
		} elseif ( is_array($value) ){
			foreach($value as &$tmp){
				if (is_string($tmp)) $tmp = trim($tmp);
			}
			$this->attachment = $value;
			return true;
		}
		return null;
	}

	public function setCategory($value){
		if(!isset($value)) return null;

		if(is_string($value)){
			if ( !$value ) return null;

			$value = \Base\Utils::m_explode([";",","],$value);
			foreach($value as &$val){
				$val = intval($val);
			}
			$this->category = $value;
			return true;
		} elseif ( is_array($value) ){
			foreach($value as &$tmp){
				if (is_string($tmp)) $tmp = trim($tmp);
			}
			$this->category = $value;
			return true;
		}
		return null;
	}

	function hasValue($key){

		$aliases = Array(
			"test_a" => "test_b",
			"test_b" => "test_c"
		);

		$methodMap = Array(
			"test_b" => "M1"
		);

		$propMap = Array(
			"test_c" => "P1"
		);

		$key = strtr($key, $aliases);

		if ( array_key_exists($key, $methodMap) ) {

			return true;

		} elseif ( array_key_exists($key, $propMap) ) {

			return true;

		} elseif (  isset($this->$key)  ){

			return true;

		}

		return false;

	}

	function getValue($key){

		$aliases = Array(
			"test_a" => "test_b",
			"test_b" => "test_c"
			// На случай переименования
			// или в случае если значение станет результатом выполнения функции.
		);

		$methodMap = Array(
			"test_b" => "M1"
			// Карта действий.
			// Опр. ведет ли ключ на метод класса.
		);

		$propMap = Array(
			"test_c" => "P1"
		);

		$key = strtr($key, $aliases);

		if ( array_key_exists($key, $methodMap) ) {

			$method = $methodMap[$key];
			return $this->$method();

		} elseif ( array_key_exists($key, $propMap) ) {

			$prop = $propMap[$key];
			return $this->$prop;

		} elseif (  isset($this->$key)  ){

			return $this->$key;

		}

		return null;

	}



	function setValue($key, $value){

		$aliases = Array(
			"test_a" => "test_b",
			"test_b" => "test_c"
			// На случай переименования
			// или в случае если значение станет результатом выполнения функции.
		);

		$propMap = Array(
			"test_c" => "P1"
		);

		$methodMap = Array(
			"attachment" => "setAttachment"
			// Карта действий.
			// Опр. ведет ли ключ на метод класса.
		);

		$key = strtr($key, $aliases);

		// TODO Ввести проверку типов данных.

		if ( array_key_exists($key, $methodMap) ) {

			$method = $methodMap[$key];
			return $this->$method($value);

		} elseif ( array_key_exists($key, $propMap) ) {

			$prop = $propMap[$key];
			$this->$prop = $value;
			return true;

		} elseif ( property_exists($this,$key) ) {

			$this->$key = $value;
			return true;

		}

		return null;

	}


	function save(){

		if ( !isset($this->db) ) {
			$this->lastError = "Undefined DB";
			return null;
		}

		if (!isset($this->name, $this->status, $this->measure)){
			$this->lastError = "Undefined: name, status, measure";
			return null;
		}

		$fieldAliases = Array();

		$values = Array("id","name","price","description","amount","measure","status");

		if (!$this->getValue("id")){
			$this->setValue("id", $this->getNewID());
		}

		$SQLFields = Array("regdate", "editdate");

		$SQLValues = Array("NOW()", "NOW()");

		foreach($values as $key){
			$value = $this->getValue($key);

			$field = strtr($key,$fieldAliases);

			if($value !== null){
				if ( is_string($value) ) {
					$value = filter_var($value, FILTER_SANITIZE_MAGIC_QUOTES);
					$value = filter_var($value, FILTER_SANITIZE_STRING);
				}

				$SQLFields[] = $field;
				$SQLValues[] = "'" . $value . "'";
			}
		}

		// Запись в таблицу Products
		$SQL = "
		INSERT INTO products (" . implode(',', $SQLFields) . ")
		VALUES (" . implode(",", $SQLValues) . ")
		";

		$dbres = $this->db->query(Array("query"=>$SQL));

		if (count($dbres["err"])){
			$this->lastError = "DB Err: " . implode($dbres["err"]);
			return null;
		}

		// Запись информации о прикреплениях
		if ($attachments = $this->getValue("attachment")) {
			$tmp = Array();
			if ( is_array($attachments) && count($attachments)) {
				foreach ($attachments as $attachment) {
					$tmp[] = "(" . $this->id . ", 'product', 'attachment'," . $attachment . ")";
				}
				$SQL = "INSERT INTO properties (pid,class,property,value_1) VALUES " . implode(", ", $tmp);
				$this->db->query(Array("query" => $SQL));
			}
		}

		if (count($dbres["err"])){
			$this->lastError = "DB Err: " . implode($dbres["err"]);
			return null;
		}

		// Запись информации о категориях
		if ($categories = $this->getValue("category")){
			$tmp = Array();
			if ( is_array($categories) && count($categories) ){
				foreach($categories as $category){
					$tmp[] = "(" . $this->id . ", 'product', 'category'," . $category . ")";
				}
				$SQL = "INSERT INTO properties (pid,class,property,value_1) VALUES " . implode(", ", $tmp);
				$this->db->query(Array("query"=>$SQL));
			}
		}

		if (count($dbres["err"])){
			$this->lastError = "DB Err: " . implode($dbres["err"]);
			return null;
		}

	}


	function update($arg = Array()){

		// Если не указан ID товара
		if (  !isset($this->id)  ){
			$this->lastError = "Undefined product ID";
			return null;
		}

		// Не подключена БД
		if (  !isset($this->db)  ){
			$this->lastError = "Undefined DB";
			return null;
		}

		// --------------- Определение полей подлежащих записи

		$fields = Array();

		if ( isset($arg["fields"]) ) {

			$fields = $this->utils->parseArgument(
				Array(
					"into" => "array",
					"delimiters" => [",",";"],
					"kickEmpty" => true,
					"value" => $arg["fields"]
				)
			);

		}

		// ------------------------------------------------------------

		if ( is_array($fields) && count($fields) ) {

			$fieldAliases = Array();

			$deniedFields = Array("id","editdate");

			$skipFields = Array("attachment","category");

			$SQLSetValues = Array("editdate = NOW()");

			foreach($fields as $field){
				if ( in_array(strtolower($field), $deniedFields) ) continue;

				if ( in_array(strtolower($field), $skipFields) ) continue;

				if (!$this->hasValue($field)) continue;

				$value = $this->getValue($field);

				$field = strtr($field,$fieldAliases);

				if ($value === null) {
					$SQLSetValues[] = $field . " = null";
				} else {
					if ( is_string($value) ) {
						$value = filter_var($value, FILTER_SANITIZE_MAGIC_QUOTES);
						$value = filter_var($value, FILTER_SANITIZE_STRING);
					}
					$SQLSetValues[] = $field . " = '" . $value . "'";
				}

			}

			// ------------------------------------------------------------

			$SQL = "
			UPDATE products
			SET " . implode(', ', $SQLSetValues) . "
			WHERE id = " . $this->id;

			$dbres = $this->db->query(
				Array(
					"query" => $SQL
				)
			);

			if ( count($dbres["err"]) ){
				$this->lastError = "DB Err: " . implode("; ",$dbres["err"]);
				return false;
			}

			// ------------------------------------------------------------

			if (in_array("attachment",$fields)){

				if ($attachments = $this->getValue("attachment")){
					$SQL = "
					DELETE FROM properties
					WHERE
						pid = " . $this->id . "
						AND class = 'product'
						AND property = 'attachment'
					";
					$this->db->query(Array("query"=>$SQL));

					$tmp = Array();
					foreach($attachments as $attachment){
						$tmp[] = "(" . $this->id . ",'product','attachment'," . $attachment . ")";
					}

					$SQL = "
					INSERT INTO properties (pid,class,property,value_1) VALUES " . implode(',',$tmp) . "
					";

					$this->db->query(Array("query"=>$SQL));
				}

			}

			// TODO Проверить

			// ------------------------------------------------------------

			if (in_array("category",$fields)){

				if ($categories = $this->getValue("category")){

					$SQL = "
					DELETE FROM properties
					WHERE
						pid = " . $this->id . "
						AND class = 'product'
						AND property = 'category'
					";
					$this->db->query(Array("query"=>$SQL));

					$tmp = Array();
					foreach($categories as $category){
						$tmp[] = "(" . $this->id . ",'product','category'," . $category . ")";
					}

					$SQL = "
					INSERT INTO properties (pid,class,property,value_1) VALUES " . implode(',',$tmp) . "
					";

					$this->db->query(Array("query"=>$SQL));
				}

				// ------------------------------------------------------------

			}

			return true;

		}

		return null;

	} // close.update

	public function getNewID(){
		if(!isset($this->db)){
			$this->lastError = "Undefined DB";
			return null;
		}

		$dbres = $this->db->query(
			Array(
				"query" => "SELECT IF(MAX(id) IS NULL,1,MAX(id)+1) as id FROM products"
			)
		);

		$id = $dbres["rows"][0]["id"];

		return $id;
	}

	public function delete(){

		// Если не указан ID товара
		if (  !isset($this->id)  ){
			$this->lastError = "Undefined product ID";
			return null;
		}

		// Не подключена БД
		if (  !isset($this->db)  ){
			$this->lastError = "Undefined DB";
			return null;
		}

		$SQL = "DELETE FROM products WHERE id = " . $this->id;

		$dbres = $this->db->query(Array("query" => $SQL));

		if(count($dbres["err"])){
			$this->lastError = "DB Err: " . implode("; ", $dbres["err"]);
			return null;
		}

		$SQL = "DELETE FROM properties WHERE class = 'product' AND pid = " . $this->id;

		$dbres = $this->db->query(Array("query" => $SQL));

		if(count($dbres["err"])){
			$this->lastError = "DB Err: " . implode("; ", $dbres["err"]);
			return null;
		}

		return true;

	}


} // close.class