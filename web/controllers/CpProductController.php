<?php

class CpProductController extends \Base\Controller {
	public function editProducts(){

		$auth = new Auth();
		if (!$auth->login()) return null;

		$productsModel = new ProductsModel();

		$status = (isset($_GET["status"]) && $_GET["status"] ? $_GET["status"] : null);

		if ( isset($_GET["page"]) ){
			$page = intval($_GET["page"]);

			if ( isset($_GET["perpage"]) ) $perpage = intval($_GET["perpage"]);

			if (!isset($perpage) || $perpage == 0) $perpage = 24;

			if ($page > 0){
				$limit = ["from"=>($page - 1) * $perpage, "amount"=>$perpage];
			} else {
				$limit = ["from"=>0, "amount"=>$perpage];
			}
		} else {
			$limit = ["from"=>0,"amount"=>24];
		}

		$products = $productsModel->getProducts(
			Array(
				"output" => "array",
				"status" => $status,
				"limit" => $limit
			)
		);

		$this->view->render(
			"page.php",
			Array(
				"_bodyTemplateFile" => rtrim($this->view->viewBasePath,"\\/") . "/" . "cpProducts.php",
				"products" => $products,
				"err" => $productsModel->lastError
			)
		);

	}



	public function editProduct(){

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

		$productsModel = new ProductsModel();
		$categoriesModel = new CategoriesModel();
		$attachmentsModel = new AttachmentsModel();

		$categories = $categoriesModel->getCategories(Array("output"=>"array"));

		if ( $id == "new" ){

			$product = new ProductModel();

			$product->setValue("id",$product->getNewID());
			$product->setValue("name", "Новая запись");
			$product->setValue("price", 0);

			$this->view->render(
				"page.php",
				Array(
					"_bodyTemplateFile" => rtrim($this->view->viewBasePath,"\\/") . "/" . "cpProduct.php",
					"actionType" => "add",
					"product" => &$product,
					"categories" => $categories,
					"err" => $productsModel->lastError
				)
			);
			return null;

		} else {

			$product = $productsModel->getProducts(Array("id"=>$id,"output" => "class-object"));

			if ( !count($product) ){
				$this->view->render(
					"jsonView.php",
					Array("err" => "Product does not exist")
				);
			}

			$product = $product[0];

			$productAttachments = $product->getValue("attachment");

			$attachments = $attachmentsModel->getAttachments(Array("id"=>$productAttachments));
			$attachmentsLnk = Array();

			foreach($attachments as &$tmp){
				$attachmentsLnk[$tmp["id"]] = &$tmp;
			}

			foreach($productAttachments as &$tmp){
				$tmp = $attachmentsLnk[$tmp];
			}

			$product->setValue("attachment",$productAttachments);

			$this->view->render(
				"page.php",
				Array(
					"_bodyTemplateFile" => rtrim($this->view->viewBasePath,"\\/") . "/" . "cpProduct.php",
					"actionType" => "update",
					"product" => &$product,
					"categories" => $categories,
					"err" => $productsModel->lastError
				)
			);

		}

	}



	public function addProduct(){

		$auth = new Auth();
		if (!$auth->login()) return null;

		$core = new \Base\Core();
		$request = $core->get("request");
		$PUT = $request->getPUT();
		$utils = new Utils();

		if ( $PUT ) {

			if (
				!isset(
					$PUT["name"],
					$PUT["description"],
					$PUT["category"],
					$PUT["price"],
					$PUT["amount"],
					$PUT["status"]
				)
			){
				$this->view->render(
					"jsonView.php",
					Array("err"=>"!name,description,category,price,amount,category")
				);
				return null;
			} else {

				$name = str_replace(["'", '"',"\\","/"],"",trim($PUT["name"]));

				$category  = $utils->parseArgument(Array(
					"into" => "array",
					"value" =>$PUT["category"],
					"isInt" => true,
					"kickEmpty" => true
				));

				$description = htmlspecialchars($PUT["description"], ENT_QUOTES);

				$price = floatval(str_replace(",",".",$PUT["price"]));

				$amount = intval($PUT["amount"]);

				$measure = ( isset($PUT["measure"]) ? str_replace(["'",'"',"\\","/"],"",$PUT["measure"]) : "" );

				if (
					isset($PUT["status"])
					&& is_string($PUT["status"])
					&& in_array( strtolower($PUT["status"]), Array("published","draft") )
				){
					$status = strtolower($PUT["status"]);
				} else {
					$status = "published";
				}

				if ( isset($PUT["attachments"]) ) {
					$attachments = $utils->parseArgument(Array(
						"into"=>"array",
						"value"=>$PUT["attachments"],
						"isInt"=>true,
						"kickEmpty"=>true,
						"delimiters"=>[";",","]
					));
					if (!$attachments) $attachments = Array();
				} else {
					$attachments = Array();
				}

				// ......................................................

				$product = new ProductModel();
				$product->setValue("id",$product->getNewID());
				$product->setValue("name",$name);
				$product->setValue("status",$status);
				$product->setValue("category",$category);
				$product->setValue("price",$price);
				$product->setValue("amount",$amount);
				$product->setValue("measure",$measure);
				$product->setValue("description",$description);
				$product->setValue("attachment",$attachments);
				$product->save();

				$this->view->render(
					"jsonView.php",
					Array(
						"err"=>$product->lastError,
						"res"=>Array(
							"err"=>$product->lastError
						)
					)
				);

			}

		}

	}



	public function updateProduct(){

		$auth = new Auth();
		if (!$auth->login()) return null;

		$utils = new Utils();

		if ( isset($this->arguments["id"]) ){
			$id = $this->arguments["id"];
			if( preg_match('/\D/',trim($id)) ) return null;
		} else {
			return null;
		}

		if ( $_POST ){

			if (
				!isset(
					$_POST["name"],
					$_POST["description"],
					$_POST["category"],
					$_POST["price"],
					$_POST["amount"],
					$_POST["status"]
				)
			) {

				$tmp = Array("name", "description", "category", "price", "amount", "status");
				$tmp2 = Array();
				foreach($tmp as &$tmp_){
					if (  !isset($_POST[$tmp_])  ){
						$tmp2[] = $tmp_;
					}
				}

				$this->view->render(
					"jsonView.php",
					Array("err"=>"!" . implode(",",$tmp2))
				);

			} else {

				$name = str_replace(["'", '"',"\\","/"],"",trim($_POST["name"]));

				$category  = $utils->parseArgument(Array(
					"into" => "array",
					"value" =>$_POST["category"],
					"isInt" => true,
					"kickEmpty" => true
				));

				$description = htmlspecialchars($_POST["description"], ENT_QUOTES);

				$price = floatval(str_replace(",",".",$_POST["price"]));

				$amount = intval($_POST["amount"]);

				$measure = ( isset($_POST["measure"]) ? str_replace(["'",'"',"\\","/"],"",$_POST["measure"]) : "" );

				if (
					isset($_POST["status"])
					&& is_string($_POST["status"])
					&& in_array( strtolower($_POST["status"]), Array("published","draft") )
				){
					$status = strtolower($_POST["status"]);
				} else {
					$status = "published";
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

				// ......................................................

				$productsModel = new ProductsModel();

				$product = $productsModel->getProducts(Array("id"=>$id, "output"=>"class-object"));

				if ( !count($product) ){
					$this->view->render("jsonView.php", Array("err"=>"Product does not exist"));
					return null;
				}

				// ......................................................

				$product = $product[0];

				$product->setValue("name",$name);
				$product->setValue("status",$status);
				$product->setValue("category",$category);
				$product->setValue("price",$price);
				$product->setValue("amount",$amount);
				$product->setValue("measure",$measure);
				$product->setValue("description",$description);
				$product->setValue("attachment",$attachments);

				$product->update(
					Array(
						"fields"=>"name,category,price,amount,measure,description,attachment"
					)
				);

				$this->view->render(
					"jsonView.php",
					Array(
						"err"=>$product->lastError,
						"res"=>Array(
							"err"=>$product->lastError
						)
					)
				);

			}

		}

	}



	public function deleteProduct(){

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

		} elseif ( isset($DELETE["id"]) ) {

			$id = $utils->parseArgument(Array(
				"into"=>"array",
				"value"=>$DELETE["id"],
				"delimiters"=>[";",","],
				"isInt"=>true,
				"kickEmpty"=>true
			));

		} else {
			return null;
		}

		$ProductsModel = new ProductsModel();

		$ProductsModel->delete(Array("id"=>$id));

		$this->view->render("jsonView.php",
			Array(
				"res"=>Array(
					"err" => $ProductsModel->lastError
				),
				"err"=>$ProductsModel->lastError
			)
		);

	}
}