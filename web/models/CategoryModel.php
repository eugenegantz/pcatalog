<?php

class CategoryModel {

	public $lastError = null;

	public $db;

	private $id, $name, $parent, $attachment = Array();

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

		if (isset($arg["categoriesModel"])){
			$this->categoriesModel = $arg["categoriesModel"];
		} else {
			$this->categoriesModel = new CategoriesModel();
		}

		if (isset($arg["productsModel"])){
			$this->productsModel = $arg["productsModel"];
		} else {
			$this->productsModel = new ProductsModel();
		}

		$this->utils = new Utils();

	}

	public function setAttachment($value){
		if(!isset($value)) return null;

		if(is_string($value)){
			$value = \Base\Utils::m_explode([";",","],$value);
			foreach($value as &$val){
				$val = intval($val);
			}
			$this->attachment = $value;
			return true;
		} elseif ( is_array($value) ){
			$this->attachment = $value;
			return true;
		}
		return null;
	}

	function hasValue($key){

		$aliases = Array();

		$methodMap = Array();

		$propMap = Array();

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
			// На случай переименования
			// или в случае если значение станет результатом выполнения функции.
		);

		$methodMap = Array(
			// Карта действий.
			// Опр. ведет ли ключ на метод класса.
		);

		$propMap = Array(

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
			// На случай переименования
			// или в случае если значение станет результатом выполнения функции.
		);

		$propMap = Array(

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

	public function getTree(){

		if ( !isset($this->id) ){
			$this->lastError = "Undefined ID";
			return null;
		}

		if (  !isset($this->db)  ){
			$this->lastError = "Undefined DB";
			return null;
		}

		$SQL = "

		";

		$dbres = $this->db->query(
			Array(
				"query"=>$SQL
			)
		);

		if (count($dbres["err"])){
			$this->lastError = "DB Err: " . implode(',',$dbres["err"]);
			return null;
		}

		// TODO
	}

	public function getChildren($arg = Array()){

		if ( !isset($this->id) ){
			$this->lastError = "Undefined ID";
			return null;
		}

		$arg["parent"] = $this->id;

		return $this->categoriesModel->getCategories($arg);

	}

	public function getProducts($arg = Array()){

		// Если не указан ID товара
		if (  !isset($this->id)  ){
			$this->lastError = "Undefined ID";
			return null;
		}

		// Не подключена БД
		if (  !isset($this->db)  ){
			$this->lastError = "Undefined DB";
			return null;
		}

		$arg["category"] = $this->id;

		return $this->productsModel->getProducts($arg);

		// TODO Проверить
	}


	public function update($arg = Array()){

		// Если не указан ID товара
		if (  !isset($this->id)  ){
			$this->lastError = "Undefined ID";
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

			$deniedFields = Array("id");

			$skipFields = Array("attachment");

			$SQLSetValues = Array();

			foreach($fields as $field){

				if ( in_array(strtolower($field), $skipFields) ) continue;

				if ( in_array(strtolower($field), $deniedFields) ) continue;

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
			UPDATE categories
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

			if (  in_array("attachment",$fields)  ){

				$attachments = $this->getValue("attachment");

				$SQL = "
				DELETE FROM properties
				WHERE
					pid = " . $this->id . "
					AND class = 'category'
					AND property = 'attachment'
				";
				$this->db->query(Array("query"=>$SQL));

				if( is_array($attachments) && count($attachments) ){
					$tmp = Array();
					foreach($attachments as $attachment){
						$tmp[] = "(" . $this->id . ",'category','attachment'," . $attachment . ")";
					}

					$SQL = "
					INSERT INTO properties (pid,class,property,value_1) VALUES " . implode(',',$tmp) . "
					";

					$this->db->query(Array("query"=>$SQL));
				}

			}

			// TODO Проверить

			// ------------------------------------------------------------

			return true;

		}

		return null;

	}


	public function save(){

		if ( !isset($this->db) ) {
			$this->lastError = "Undefined DB";
			return null;
		}

		if ( !isset($this->categoriesModel) ) {
			$this->lastError = "Undefined CategoriesModel";
			return null;
		}

		if (!isset($this->name)){
			$this->lastError = "Undefined: name";
			return null;
		}

		$fieldAliases = Array();

		$values = Array("id","name","parent");

		if (!$this->getValue("id")){
			$this->setValue("id", $this->getNewID());
		}

		$SQLFields = Array();

		$SQLValues = Array();

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
		INSERT INTO categories (" . implode(',', $SQLFields) . ")
		VALUES (" . implode(",", $SQLValues) . ")
		";

		$dbres = $this->db->query(Array("query"=>$SQL));

		if (count($dbres["err"])){
			$this->lastError = "DB Err: " . implode($dbres["err"]);
			return null;
		}

		// Запись информации о прикреплениях
		if ($attachments = $this->getValue("attachment")) {
			if (is_array($attachments) && count($attachments)) {
				$tmp = Array();
				foreach ($attachments as $attachment) {
					$tmp[] = "(" . $this->id . ", 'category', 'attachment'," . $attachment . ")";
				}
				$SQL = "INSERT INTO properties (pid,class,property,value_1) VALUES " . implode(", ", $tmp);
				$this->db->query(Array("query" => $SQL));
			}
		}

		if (count($dbres["err"])){
			$this->lastError = "DB Err: " . implode($dbres["err"]);
			return null;
		}

		$categoriesOrder = $this->categoriesModel->getCategoriesOrder();

		$categoriesOrder[] = $this->id;

		$this->categoriesModel->setCategoriesOrder($categoriesOrder);

		return true;

	}


	public function getNewID(){
		if(!isset($this->db)){
			$this->lastError = "Undefined DB";
			return null;
		}

		$dbres = $this->db->query(
			Array(
				"query" => "SELECT IF(MAX(id) IS NULL,1,MAX(id)+1) as id FROM categories"
			)
		);

		$id = $dbres["rows"][0]["id"];

		return $id;
	}


	public function delete(){
		// Если не указан ID товара
		if (  !isset($this->id)  ){
			$this->lastError = "Undefined ID";
			return null;
		}

		// Не подключена БД
		if (  !isset($this->db)  ){
			$this->lastError = "Undefined DB";
			return null;
		}

		if (  !isset($this->categoriesModel)  ){
			$this->lastError = "Undefined CategoriesModel";
			return null;
		}

		$SQL = "DELETE FROM categories WHERE id = " . $this->id;
		$dbres = $this->db->query(Array("query" => $SQL));

		if (count($dbres["err"])){
			$this->lastError = "DB Err: " . implode($dbres["err"]);
			return null;
		}

		$SQL = "" .
		"DELETE FROM properties " .
		"WHERE " .
			"(" .
				"class = 'category' " .
				"AND pid = " . $this->id .
			") " .
			"OR ( " .
				"class = 'product' " .
				"AND property = 'category'" .
				"AND value_1 IN (" . $this->id . ") " .
			")" .
		"";

		$dbres = $this->db->query(Array("query" => $SQL));

		if (count($dbres["err"])){
			$this->lastError = "DB Err: " . implode($dbres["err"]);
			return null;
		}

		$categoriesOrder = $this->categoriesModel->getCategoriesOrder();

		$tmp = Array();
		foreach($categoriesOrder as $tmp2){
			if( $tmp2 != $this->id ) $tmp[] = $tmp2;
		}

		$this->categoriesModel->setCategoriesOrder($tmp);

		// TODO Проверить
	}

}