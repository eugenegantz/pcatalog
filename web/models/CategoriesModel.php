<?php

class CategoriesModel {

	public $lastError = null;

	public $db, $utils;

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


	public function getCategories($arg = Array()){

		if( !isset($this->db) ){
			$this->lastError = "Undefined DB";
			return null;
		}

		$SQLConditions = Array();

		// ---------------- Формат ответа

		if ( isset($arg["output"]) && is_string($arg["output"]) ){

			$output = strtolower($arg["output"]);

			if ( !in_array($output, Array("array","class-object")) ){
				$output = "array";
			}

		} else {
			$output = "array";
		}

		// ------------------------------------------------------------

		$id = null;

		// Выборка по ID
		if ( isset($arg["id"]) ){

			$id = $this->utils->parseArgument(
				Array(
					"into" => "Array",
					"kickEmpty" => true,
					"delimiters" => [";", ","],
					"value" => $arg["id"]
				)
			);

			if (is_array($id) && count($id)) {
				$SQLConditions[] = "categories.id IN(". implode(',',$id) .")";
			}

		}

		// ------------------------------------------------------------

		// Выборка по криетрию наличия родительской
		if ( isset($arg["hasParent"]) ){
			$hasParent = $arg["hasParent"];
			if ($hasParent){
				$SQLConditions[] = "categories.parent IS NOT NULL";
			} else {
				$SQLConditions[] = "categories.parent IS NULL";
			}
		} else {
			$hasParent = false;
		}

		// ------------------------------------------------------------

		if ( isset($arg["parent"]) ){
			$parent = $this->utils->parseArgument(
				Array(
					"into"=>"array",
					"kickEmpty"=>true,
					"isInt"=>true,
					"delimiters"=>[";",","],
					"value"=>$arg["parent"]
				)
			);

			if ( is_array($parent) && count($parent) ){
				$SQLConditions[] = "categories.parent IN (" . implode(',',$parent) . ")";
			}
		} else {
			$parent = null;
		}

		// ------------------------------------------------------------

		// Выборка по криетрию наличия подкатегорий
		if ( isset($arg["hasChildren"]) ){
			$hasChildren = $arg["hasChildren"];
			if ($hasChildren){
				$SQLConditions[] = "hasChildren > 0";
			} else {
				$SQLConditions[] = "hasChildren = 0";
			}
		} else {
			$hasChildren = false;
		}

		// ------------------------------------------------------------

		// Выборка по критерию наличия товаров в категории
		if ( isset($arg["hasProducts"]) ){
			$hasProducts = $arg["hasProducts"];
			if ($hasProducts){
				$SQLConditions[] = "hasProducts > 0";
			} else {
				$SQLConditions[] = "hasProducts = 0";
			}
		} else {
			$hasProducts = false;
		}

		// ------------------------------------------------------------

		$SQL = "
		SELECT
			categories.id,
			categories.name,
			categories.parent,
			categories.description,
			count(DISTINCT ProdCat.pid) as hasProducts,
			count(Children.parent) as hasChildren,
			group_concat(DISTINCT attachments.value_1 SEPARATOR ';') as attachments
		FROM categories
		LEFT JOIN (SELECT pid,value_1 FROM properties WHERE class = 'product' AND property = 'category') as ProdCat
			ON categories.id = ProdCat.value_1
		LEFT JOIN (SELECT parent FROM categories) as Children
			ON categories.id = Children.parent
		LEFT JOIN ( SELECT pid, property, value_1 FROM properties WHERE property = 'attachment' AND class = 'category' ) as attachments
			ON categories.id = attachments.pid
		GROUP BY categories.id
		" . (count($SQLConditions) ? " HAVING " . implode(' AND ',$SQLConditions) : '' ) . "
		";

		$dbres = $this->db->query(Array("query"=>$SQL));

		if(count($dbres["err"])){
			$this->lastError = "DB Err: " . implode("; ",$dbres["err"]);
			return null;
		}

		// Получение порядка категорий
		$categoriesOrder = $this->getCategoriesOrder();

		if ( $categoriesOrder === null ){
			// $this->lastError = "Order not exist";
			$categoriesOrder = Array();
			// return null;
		}

		$sortedCategories = Array();
		$catKeys = Array();

		foreach($dbres["rows"] as $key => &$row){
			$row["attachments"] = ( $row["attachments"] ? explode(";",$row["attachments"]) : Array() );
			$catKeys[$key] = $row["id"];
		}

		// Откинуть элементы ряда, которых нет в ответе БД.
		$tmp = Array();
		foreach($categoriesOrder as $key => $value){
			if (in_array($value,$catKeys)) $tmp[] = $value;
		}

		// Включить строки БД, которых нету среди ряда.
		foreach($catKeys as $key => $value){
			if (!in_array($value,$tmp)) $tmp[] = $value;
		}

		// Приведенный ряд порядка категорий
		$categoriesOrder = $tmp;

		// Сортировка
		foreach($categoriesOrder as $value){
			if (in_array($value, $catKeys)){
				$key = array_search($value, $catKeys);
				$sortedCategories[] = &$dbres["rows"][$key];
			}
		}

		if ( $output == "array" ){

			return $sortedCategories;

		} elseif ($output == "class-object") {

			foreach( $sortedCategories as &$row ){
				$category = new CategoryModel();
				$category->setValue("id",$row["id"]);
				$category->setValue("name",$row["name"]);
				$category->setValue("parent",$row["parent"]);
				$category->setValue("attachment",$row["attachments"]);

				$row = $category;
			}

			// TODO Проверить
			return $sortedCategories;

		}

	}


	public function getCategoriesOrder(){

		if(!isset($this->db)){
			$this->lastError = "Undefined DB";
			return null;
		}

		$SQL = "SELECT value_3 FROM  properties WHERE property = 'categoriesOrder' ";

		$dbres = $this->db->query(Array("query"=>$SQL));

		if ( !count($dbres["rows"]) ){
			// $this->lastError = "This property not exist";
			// return null;
			return Array();
		}

		$order = $dbres["rows"][0]["value_3"];

		$order = $this->utils->parseArgument(
			Array(
				"into" => "array",
				"kickEmpty"=>true,
				"isInt" => true,
				"toInt" => true,
				"value" => $order
			)
		);

		return $order;

	}


	public function setCategoriesOrder($order = null){

		if(!isset($this->db)){
			$this->lastError = "Undefined DB";
			return null;
		}

		if ($order === null) {
			return null;
		}

		$order = $this->utils->parseArgument(Array(
			"into"=>"array",
			"value"=>$order,
			"isInt"=>true,
			"toInt"=>true,
			"kickEmpty"=>true
		));

		$SQL = "DELETE FROM properties WHERE property = 'categoriesOrder' ";
		$this->db->query(Array("query"=>$SQL));

		$SQL = "INSERT INTO properties (value_3, property) VALUES ('" . implode(';',$order) . "', 'categoriesOrder')";
		$this->db->query(Array("query"=>$SQL));

		return true;

	}


	public function delete($arg = Array()){

		if ( !isset($arg["id"]) ){
			$this->lastError = "Undefined IDs";
			return null;
		}

		if(!isset($this->db)){
			$this->lastError = "Undefined DB";
			return null;
		}

		// Выборка по ID
		$id = null;
		if ( isset($arg["id"]) ){

			$id = $this->utils->parseArgument(
				Array(
					"into" => "Array",
					"kickEmpty" => true,
					"delimiters" => [";", ","],
					"value" => $arg["id"]
				)
			);

			if (is_array($id) && count($id)) {
				$SQL = "DELETE FROM categories WHERE id IN(" . implode(",",$id) . ")";
				$this->db->query(Array("query" => $SQL));

				$SQL = "" .
				"DELETE FROM properties " .
				"WHERE " .
					"(" .
						"class = 'category' " .
						"AND pid IN(" . implode(",",$id) . ")" .
					") " .
					"OR ( " .
						"class = 'product' " .
						"AND property = 'category'" .
						"AND value_1 IN (" . implode(",",$id) . ") " .
					")" .
				"";
				$this->db->query(Array("query" => $SQL));
			}

			$categoriesOrder = $this->getCategoriesOrder();

			$tmp = Array();
			foreach($categoriesOrder as $tmp2){
				if( !in_array($tmp2,$id) ) $tmp[] = $tmp2;
			}

			$this->setCategoriesOrder($tmp);

		}

		// TODO Проверить

		return null;

	}

}