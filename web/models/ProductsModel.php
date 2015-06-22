<?php

class ProductsModel {

	public $db;

	public $config;

	public $lastError = null;

	public $foundRows = null;

	function __construct($arg = Array()){

		if ( isset($arg["config"]) ) {
			$this->config = $arg["config"];
		} elseif ( \Base\Core::hasGlobal("config") ) {
			$this->config = \Base\Core::getGlobal("config");
		} else {
			echo 'Не найдены настройки. / ' . __FILE__ . '/' .  __LINE__ . ' /';
			return null;
		}

		if ( isset($arg["db"]) ) {
			$this->db = $arg["db"];
		} else {

			$rootdir = \Base\Core::getGlobal("rootDir");

			$this->db = \Base\Core::getGlobal(
				"db",
				Array(
					"dbtype"=>"mysql",
					"dbname" => $this->config["db_name"],
					"dblogin" => $this->config["db_user"],
					"dbpassword" => $this->config["db_password"],
					"dbaddress" => $this->config["db_address"],
					"logfile" => $this->config["db_logfile"]
				)
			);

			$this->db = $this->db[0];
		}

		$this->utils = new Utils();

	}

	public function getProducts($arg = Array()){

		if (!isset($this->db)){
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

		// ---------------- Выбор по статусу

		$status = null;

		if ( isset($arg["status"]) ) {

			$status = $arg["status"];

			$status = $this->utils->parseArgument(
				Array(
					"into"=>"array",
					"value"=>$status,
					"delimiters"=>[";",","],
					"kickEmpty"=>true,
					"filters"=>[FILTER_SANITIZE_MAGIC_QUOTES, FILTER_SANITIZE_STRING]
				)
			);

			if (is_array($status) && count($status)) {
				$SQLConditions[] = "products.status IN ('" . implode("','", $status) . "')";
			}

		}


		// ---------------- Выбор по Категории

		$category = null;

		if ( isset($arg["category"]) ) {

			$category = $arg["category"];

			$category = $this->utils->parseArgument(
				Array(
					"into"=>"array",
					"delimiters"=>[";",","],
					"kickEmpty"=>true,
					"isInt"=>true,
					"value"=>$category
				)
			);

			if (is_array($category) && count($category)) {
				$str = "";
				$SQLConditions[] = "" .
				"products.id IN " .
				"(" .
					"SELECT pid FROM properties " .
					"WHERE " .
						"class = 'product' " .
						"AND property = 'category' " .
						"AND value_1 IN ('". implode("','", $category) ."') " .
				")";
			}

		}


		// ---------------- Выбор по ID ----------------

		$id = null;

		if ( isset($arg["id"]) ){

			$id = $arg["id"];

			$id = $this->utils->parseArgument(
				Array(
					"into"=>"array",
					"delimiters"=>[";",","],
					"kickEmpty"=>true,
					"isInt"=>true,
					"value"=>$id
				)
			);

			if (count($id)){
				$SQLConditions[] = "products.id IN(". implode(',',$id) .")";
			}

		}


		// ---------------- Выбор по цене ----------------

		$price = null;

		if ( isset($arg["price"]) ) {

			if ( is_string($arg["price"]) ){

				$price = \Base\Utils::m_explode([",", ";", "-"],trim($arg["price"]));

				foreach( $price as &$tmp ){
					$tmp = trim($tmp);
				}

				if( count($price) > 2 ){
					$price = null;
				} elseif ( count($price) == 2 ) {
					$SQLConditions[] = "products.price >= " . $price[0] . " AND products.price <= " . $price[1];
				} elseif ( count($price) == 1 ) {
					$SQLConditions[] = "products.price = " . $price[0];
				}

			} elseif ( is_array($arg["price"]) ) {

				if (
					isset(
						$arg["price"]["min"],
						$arg["price"]["max"]
					)
				){

					$SQLConditions[] = "products.price >= " . $arg["price"]["min"] . " AND products.price <= " . $arg["price"]["max"];

				} elseif ( isset($arg["price"]["min"]) ) {

					$SQLConditions[] = "products.price >= " . $arg["price"]["min"];

				} elseif (  isset($arg["price"]["max"]) ) {

					$SQLConditions[] = "products.price <= " . $arg["price"]["max"];

				}

			}

		}

		// ---------------- Лимит ----------------
		$limit = null;

		if(
			isset($arg["limit"])
			&& is_array($arg["limit"])
			&& isset($arg["limit"]["from"],$arg["limit"]["amount"])
		){
			$limit = ''
				. intval($arg["limit"]["from"])
				. ','
				. intval($arg["limit"]["amount"]);
		}

		// ---------------- Сортировка запроса ----------------
		$sortby = null;

		if ( isset($arg["sortby"]) && is_string($arg["sortby"]) ) {

			$sortby = trim($arg["sortby"]);

			if(  \Base\Utils::hasSQLWords($sortby)  ){
				$sortby = null;
			}

		}

		// ---------------- Сортировка запроса ----------------


		$SQL = "
			SELECT
			SQL_CALC_FOUND_ROWS
				products.id,
				products.name,
				products.price,
				products.regdate,
				products.editdate,
				products.description,
				products.amount,
				products.measure,
				products.status,
				group_concat(DISTINCT categories.name SEPARATOR ';') as category,
				group_concat(DISTINCT categories.id SEPARATOR ';') as category_id,
				group_concat(DISTINCT attachments.value_1 SEPARATOR ';') as attachments
			FROM products
			LEFT JOIN ( SELECT pid, property, value_1 FROM properties WHERE property = 'category' AND class = 'product' ) as propCat
				ON products.id = propCat.pid
			LEFT JOIN ( SELECT pid, property, value_1 FROM properties WHERE property = 'attachment' AND class = 'product' ) as attachments
				ON products.id = attachments.pid
			LEFT JOIN categories
				ON categories.id = propCat.value_1"
			. ( count($SQLConditions) ? " WHERE " . implode(" AND ", $SQLConditions)  : "" )
			. " GROUP BY id "
			. ( $sortby ? " ORDER BY " . $sortby . " " : "" )
			. ( $limit ? ' LIMIT ' . $limit : '' )
			. "";

		$dbres = $this->db->query(
			Array(
				"query" => $SQL
			)
		);

		// ------------------------ ОТВЕТ ------------------------

		if (count($dbres["err"])) {
			$this->lastError = implode("; ",$dbres["err"]);
			return null;
		}

		$dbres2 = $this->db->query(Array("query" => "SELECT FOUND_ROWS() as foundRows"));

		if ( count($dbres2["rows"]) ){
			$foundRows = $dbres2["rows"][0]["foundRows"];
		} else {
			$foundRows = 0;
		}

		$this->foundRows = $foundRows;

		if ( $output == "array" ) {

			// Вернуть массив

			foreach($dbres["rows"] as &$row){
				$row["foundRows"] = $foundRows;
				$row["category"] = ( $row["category"] ? explode(";",$row["category"]) : Array() );
				$row["category_id"] = ( $row["category_id"] ? explode(";",$row["category_id"]) : Array() );
				$row["attachments"] = ( $row["attachments"] ? explode(";",$row["attachments"]) : Array() );
			}

			return $dbres["rows"];

		} elseif ( $output == "class-object" ) {

			// Вернуть массив экземпляров класса ProductModel

			foreach($dbres["rows"] as &$row){

				$product = new ProductModel(Array("db" => $this->db));

				$product->setValue("id", $row["id"]);
				$product->setValue("price", $row["price"]);
				$product->setValue("name", $row["name"]);
				$product->setValue("regdate", $row["regdate"]);
				$product->setValue("editdate", $row["editdate"]);
				$product->setValue("description", $row["description"]);
				$product->setValue("amount", $row["amount"]);
				$product->setValue("measure", $row["measure"]);
				$product->setValue("status", $row["status"]);
				$product->setValue("attachment", ( $row["attachments"] ? explode(";",$row["attachments"]) : Array() ) );
				$product->setValue("category", ( $row["category_id"] ? explode(";",$row["category_id"]) : Array() )  );

				$row = $product;

			}

			return $dbres["rows"];

		}

		return null;

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

		$utils = new Utils();

		$id = $arg["id"];

		$id = $utils->parseArgument(Array(
			"into"=>"array",
			"value"=>$id,
			"isInt"=>true,
			"kickEmpty"=>true,
			"delimiters"=>[";",","]
		));

		if ( is_array($id) && count($id) ){

			$SQL = "DELETE FROM products WHERE id IN (" . implode(',', $id) . ") ";
			$this->db->query(Array("query" => $SQL));

			$SQL = "DELETE FROM properties WHERE class = 'product' AND pid IN (" . implode(',', $id) . ") ";
			$this->db->query(Array("query" => $SQL));

		} else {
			return null;
		}

		return true;

	}


}