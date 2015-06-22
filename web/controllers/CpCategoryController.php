<?php


class CpCategoryController extends \Base\Controller {

	public function editCategories(){

		// -------------- Показать список категорий --------------

		$auth = new Auth();
		if (!$auth->login()) return null;

		$attachmentsModel = new AttachmentsModel();

		$categoriesModel = new CategoriesModel();

		$categories = $categoriesModel->getCategories(Array("output"=>"array"));

		$tmp = Array();
		foreach($categories as $cat){
			foreach($cat["attachments"] as $attach){
				if($attach) $tmp[] = $attach;
			}
		}

		$attachments = $attachmentsModel->getAttachments(Array("id"=>$tmp));
		$attachmentsLnk = Array();

		foreach($attachments as &$tmp){
			$attachmentsLnk[$tmp["id"]] = &$tmp;
		}

		foreach($categories as &$tmp){
			foreach($tmp["attachments"] as &$tmp2){
				$tmp2 = $attachmentsLnk[$tmp2];
			}
		}

		$this->view->render(
			"page.php",
			Array(
				"_bodyTemplateFile" => rtrim($this->view->viewBasePath,"\\/") . "/" . "cpCategories.php",
				"categories" => $categories,
				"err" => $categoriesModel->lastError
			)
		);

	}





	public function editCategory(){

		// -------------- Показать категорию для редактирования --------------

		$auth = new Auth();
		if (!$auth->login()) return null;

		if ( isset($this->arguments["id"]) ) {
			$id = strtolower($this->arguments["id"]);
			if ( $id  != "new" ){
				$id = intval($this->arguments["id"]);
			}
		} else {
			$id = null;
		}

		// ---------------------------------------------------------------------------

		$attachmentsModel = new AttachmentsModel();

		$categoriesModel = new CategoriesModel();

		// ---------------------------------------------------------------------------

		if ($id == "new"){

			$category = new CategoryModel();

			$category->setValue("id", $category->getNewID());

			$category->setValue("name", "Новая категория");

			$this->view->render(
				"page.php",
				Array(
					"_bodyTemplateFile" => rtrim($this->view->viewBasePath,"\\/") . "/" . "cpCategory.php",
					"actionType" => "add",
					"category" => $category,
					"err" => $category->lastError
				)
			);

		} else {

			$category = $categoriesModel->getCategories(
				Array(
					"id"=>$id,
					"output"=>"class-object"
				)
			);

			// Такой категории не найдено
			if ( !count($category) ){
				$this->view->render(
					"jsonView.php",
					Array(
						"err" => "Category does not exist"
					)
				);
				return null;
			}

			// ................................................................

			$category = $category[0];

			$categories = $categoriesModel->getCategories(Array("output"=>"array"));

			$categoryAttachments = $category->getValue("attachment");

			$attachments = $attachmentsModel->getAttachments(Array("id"=>$categoryAttachments));
			$attachmentsLnk = Array();

			foreach($attachments as &$tmp){
				$attachmentsLnk[$tmp["id"]] = &$tmp;
			}

			foreach($categoryAttachments as &$tmp){
				$tmp = $attachmentsLnk[$tmp];
			}

			$category->setValue("attachment",$categoryAttachments);

			$this->view->render(
				"page.php",
				Array(
					"_bodyTemplateFile" => rtrim($this->view->viewBasePath,"\\/") . "/" . "cpCategory.php",
					"category" => &$category,
					"categories" => &$categories,
					"actionType" => "update",
					"err" => $categoriesModel->lastError
				)
			);

		}

	}





	public function updateCategoryOrder(){

		// -------------- Обновить порядок категорий --------------

		$auth = new Auth();
		if (!$auth->login()) return null;

		$utils = new Utils();

		if ( isset($_POST["categoriesOrder"]) ){

			$categoriesModel = new CategoriesModel();

			$categoriesOrder = $_POST["categoriesOrder"];

			$categoriesOrder = $utils->parseArgument(Array(
				"into"=>"array",
				"value"=>$categoriesOrder,
				"kickEmpty"=>true,
				"isInt"=>true,
				"toInt"=>true,
				"delimiters"=>[",",";"]
			));

			$categoriesModel->setCategoriesOrder($categoriesOrder);

			$testCategoriesOrder = $categoriesModel->getCategoriesOrder();

			$this->view->render("jsonView.php",
				Array(
					"res"=>Array(
						"categoriesOrder" => $testCategoriesOrder
					),
					"err"=>$categoriesModel->lastError
				)
			);

		}

	}





	public function updateCategory(){

		$utils = new Utils();

		$auth = new Auth();
		if (!$auth->login()) return null;

		if ( isset($this->arguments["id"]) ) {

			// ------------------------------ Обновить конкретную категорию

			$id = $this->arguments["id"];

			if( preg_match('/\D/',trim($id)) ) return null;

			// ....................................

			$id = intval($this->arguments["id"]);

			$err = Array();

			if ( !isset($_POST["name"]) || !$_POST["name"] ){
				$err[] = "!name";
			} else {
				$name = str_replace(['"',"'","\\","/"],"",trim($_POST["name"]));
			}

			if ( isset($_POST["attachments"]) ) {
				$attachments = $utils->parseArgument(Array(
					"into"=>"array",
					"value"=>$_POST["attachments"],
					"isInt"=>true,
					"kickEmpty"=>true,
					"delimiters"=>[";",","]
				));
				if (!$attachments) $attachments = Array();
			} else {
				$attachments = Array();
			}

			// ....................................

			$categoriesModel = new CategoriesModel();
			$category = $categoriesModel->getCategories(Array("id"=>$id,"output"=>"class-object"));
			if ( !count($category) ){
				$this->view->render("jsonView.php",
					Array("err"=>"Category does not exist")
				);
				return null;
			}

			$category = $category[0];

			// ....................................

			$category->setValue("name", $name);
			$category->setValue("attachment", $attachments);
			$category->update(Array("fields"=>"name;attachment"));

			if ($category->lastError){
				$err[] = $category->lastError;
			}

			$this->view->render("jsonView.php",
				Array(
					"res"=>Array(
						"err" => implode('',$err)
					),
					"err"=>$err
				)
			);

		}

	}





	public function addCategory(){

		// -------------- Добавить категорию --------------

		$auth = new Auth();
		if (!$auth->login()) return null;

		$core = new \Base\Core();

		$req = $core->get("request");

		$utils = new Utils();

		$PUT = $req->getPUT();

		if ($PUT === null){
			return null;
		}

		$err = Array();

		// ------------------------------------------------------------------------------------------
		// Если имя не указано, задать неправильное, пустое имя

		if (  !isset($PUT["name"])  ){
			$name = "";
		} else {
			$name = $PUT["name"];;
		}

		$name = str_replace(['"',"'","\\","/"],"",trim($name));

		if ( !$name ) $err[] = "!name";

		// ------------------------------------------------------------------------------------------

		$attachments = ( isset($PUT["attachments"]) ? $PUT["attachments"] : Array() );

		$attachments = $utils->parseArgument(Array(
			"into"=>"array",
			"value"=>$attachments,
			"isInt"=>true,
			"kickEmpty"=>true,
			"delimiters"=>[";",","]
		));

		// ------------------------------------------------------------------------------------------



		// ------------------------------------------------------------------------------------------

		if ( $err ){

			$this->view->render("jsonView.php",
				Array(
					"res"=>Array(
						"err" => implode(";", $err)
					),
					"err" => implode(";", $err)
				)
			);

		} else {

			$category = new CategoryModel();
			$id = $category->getNewID();
			$category->setValue("id", $id);
			$category->setValue("name", $name);
			$category->setValue("attachment", $attachments);
			$category->save();

			if ($category->lastError){
				$err[] = $category->lastError;
			}

			$this->view->render("jsonView.php",
				Array(
					"res"=>Array(
						"err" => implode('',$err),
						"id" => $id
					),
					"err"=>$err
				)
			);

		}

	}




	public function deleteCategory(){

		$auth = new Auth();
		if (!$auth->login()) return null;

		$core = new \Base\Core();

		$req = $core->get("request");

		$utils = new Utils();

		$DELETE = $req->getDELETE();

		if ($DELETE === null) return null;

		$err = Array();

		if ( isset($this->arguments["id"]) ) {

			$id = $this->arguments["id"];

			if( preg_match('/\D/',trim($id)) ) return null;

			$id = intval($this->arguments["id"]);

		} else {
			return null;
		}

		// Данные о переносе позиций в другую категорию
		$substitute = null;
		if (
			isset($DELETE["substitute"])
			&& (
				is_string($DELETE["substitute"])
				|| is_integer($DELETE["substitute"])
			)
		){
			$substitute = $DELETE["substitute"] . "";
			if( !preg_match('/\D/',trim($substitute)) ) {
				$substitute = intval($substitute);
			} else {
				$substitute = null;
			}
		}

		$categoriesModel = new CategoriesModel();

		$productsModel = new ProductsModel();

		$products = $productsModel->getProducts(Array("category"=>$id,"output"=>"array"));

		$productsToDelete = Array();

		foreach($products as $product){
			if ( count($product["category_id"]) == 1 ){
				$productsToDelete[] = $product["id"];
			}
		}

		if ( $substitute === null ) {
			$productsModel->delete(Array("id"=>$productsToDelete));
		} else {
			$config = \Base\Core::getGlobal("config");
			$db = \Base\Core::getGlobal(
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

			$db = $db[0];

			$SQL = "UPDATE properties SET value_1 = " . $substitute . " WHERE class = 'product' AND property = 'category' AND value_1 IN (" . $id . ")";

			$dbres = $db->query(Array("query"=>$SQL));
			if (count($dbres["err"])){
				$err[] = "DB Err: " . implode($dbres["err"]);
			}
		}

		$categoriesModel->delete(Array("id" => $id));

		if ( $productsModel->lastError ) $err[] = $productsModel->lastError;

		if ($categoriesModel->lastError) $err[] = $categoriesModel->lastError;

		if (!count($err)){
			$err = null;
		}

		$this->view->render("jsonView.php",
			Array(
				"res"=>Array(
					"err" => $err
				),
				"err"=>$err
			)
		);

	}

}