<?php

class TestController extends \Base\Controller {

	function M0(){

		if ( isset($this->arguments["method"]) ){
			$method = $this->arguments["method"];
			$this->$method();
		} else {
			echo "Method does not exist";
		}

	}

	function M1(){

		// Экземпляр Класса Core
		$core = new Base\Core();

		// Установка класса testclass в качестве класса для БД по-умолчанию
		$core->set("db","testclass");

		// Вызов экземпляра БД из Core в контексте экземпляра
		$db = $core->get(
			"db",
			Array(
				"dbaddress" => "127.0.0.1",
				"dblogin" => "root",
				"dbpassword" => "",
				"dbname" => "test",
				"dbtype" => "mysql"
			)
		);

		// Вызов БД из Core в глобальном контексте
		$db_global = Base\Core::getGlobal(
			"db",
			Array(
				"dbaddress" => "127.0.0.1",
				"dblogin" => "root",
				"dbpassword" => "",
				"dbname" => "test",
				"dbtype" => "mysql"
			)
		);

		// Получение корневого пути
		$root = $core->get("rootDir");

		// Получение корневого URL
		$url = $core->get("rootURL");

		// Демонстрация
		echo "root: " . $root . "\n";

		echo "URL: " . $url . "\n\n";

		print_r($db);

		print_r($db_global);

		// Установка Класса testclass в качестве БД по-умолчанию для глобального контекста
		Base\Core::setGlobal("db","testclass");

		// Вызов БД из Core в глобальном контексте
		$db_global = Base\Core::getGlobal(
			"db",
			Array(
				"dbaddress" => "127.0.0.1",
				"dblogin" => "root",
				"dbpassword" => "",
				"dbname" => "test",
				"dbtype" => "mysql"
			)
		);

		// Демонстрация
		print_r($db_global);

		// Экземпляр Класса Core
		$core = new Base\Core();

		// Вызов экземпляра БД из Core в контексте экземпляра
		$db = $core->get(
			"db",
			Array(
				"dbaddress" => "127.0.0.1",
				"dblogin" => "root",
				"dbpassword" => "",
				"dbname" => "test",
				"dbtype" => "mysql"
			)
		);

		// Демонстрация
		print_r($db_global);

		// Не имея собственного метода БД, экземпляр обращается к глобальному,
		// который уже переопределен

	}


	public function M2() {

		$core = new \Base\Core();

		$request = $core->get("request");

		print_r($request);

	}

	public function M3(){

		header("Content-type: text/plain");

		$core = new \Base\Core();
		$config = $core->get("config");
		$db = $core->get(
			"db",
			Array(
				"dbtype" => "mysql",
				"dblogin" => $config["db_user"],
				"dbpassword" => $config["db_password"],
				"dbname" => $config["db_name"],
				"dbaddress" => $config["db_address"]
			)
		);
		$db = $db[0];

		$categories = [1,2,3,4,5,6,7,8];
		$cL = count($categories)-1;

		// ---------------------------------------------------

		$SQL = Array();
//
//		for($c=1; $c<=128; $c++){
//			$status = "published";
//			if ($c > 117) $status = "draft";
//			$SQL[] = "" .
//			"INSERT INTO Products " .
//			"(name,regdate,editdate,description,amount,measure,status) " .
//			"VALUES ('Product ". $c ."', NOW(), NOW(), 'description ". $c ."', 100, 'шт.', '". $status ."')";
//		}

		// ---------------------------------------------------

		$dbres = $db->query(
			Array(
				"query" => "SELECT * FROM Products;"
			)
		);

		$products = $dbres["rows"];

		$dbres = $db->query(
			Array(
				"query" => "SELECT * FROM Attachments;"
			)
		);

		$attachments = $dbres["rows"];
		$aL = count($attachments)-1;

		foreach($products as $product){
			$id = $product["id"];

			$category_id = $categories[mt_rand(0,$cL)];
			$SQL[] = "" .
				"INSERT INTO Properties " .
				"(pid, class, property, value_1) " .
				"VALUES (". $id .", 'product', 'category', ". $category_id .")";

			for($c=0; $c<4; $c++){
				$attachment_id = $attachments[mt_rand(0,$aL)]["id"];
				$SQL[] = "" .
				"INSERT INTO Properties " .
				"(pid, class, property, value_1) " .
				"VALUES (". $id .", 'product', 'attachment', ". $attachment_id .")";
			}
		}

		$SQL = implode(";\n", $SQL);
		echo $SQL;

		// ---------------------------------------------------

		// $dbres = $db->query(Array("query"=>"SELECT NOW();"));
		// print_r($dbres);
	}


	public function M4(){

		header("Content-type: text/plain");

		$data = [
			"f1" => "123456",
			"f2" => "",
			"f3" => "alphabeta",
			"f4" => Array("23","24"),
			"f5" => Array("19","24"),
			"f6" => Array(0,1,2,3,"","",null,"","99"),
			"f7" => "qwerty",
			"f8" => "q"
		];

		$conditions = Array(
			"f1" => Array("required"=>true, "isInteger"=>true, "isEmpty"=>false),
			"f2" => Array("required"=>true, "isEmpty"=>true),
			"f3" => Array("required"=>true, "toUpperCase"=>true, "string:contain"=>Array("Alpha","ALPHA")),
			"f4" => Array("required"=>true, "numeric:range"=>"20,35"),
			"f5" => Array("required"=>true, "numeric:range"=>["from"=>12,"to"=>35]),
			"f6" => Array("kickEmpty"=>true, "array:contain"=>Array("99")),
			"f7" => Array("string:maxLength"=>5),
			"f8" => Array("string:minLength"=>3)
		);

		$dv = new \Base\DataValidator($conditions);

		$dvres = $dv->validate($data);

		print_r($dvres);
	}


}