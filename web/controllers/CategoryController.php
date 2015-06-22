<?php

class CategoryController extends \Base\Controller {
	public function getCategory(){

		// Модель данных для получения прикреплений
		$attachmentsModel = new AttachmentsModel();

		// Модель данных для получения Категорий
		$categoriesModel = new CategoriesModel();

		// id категории из аргумента по шаблону роутов
		if ( isset($this->arguments["id"]) ){
			$id = intval($this->arguments["id"]);
		} else {
			$id = null;
		}

		$err = null;

		// Если ID не указан, выводить общий список категорий
		if ($id){
			$categories = $categoriesModel->getCategories(Array("parent"=>$id, "output"=>"array"));
			$categoryModel = $categoriesModel->getCategories(Array("id"=>$id, "output"=>"class-object"));
			if ( count($categoryModel) ) {
				$categoryModel = $categoryModel[0];
			} else {
				$err = "Category does not exist";
			}
		} else {
			$categories = $categoriesModel->getCategories(Array("hasParent"=>false,"output"=>"array"));
		}

		// --------------------------------------------------

		if( $err ){

			$this->view->render(
				"jsonView.php",
				Array(
					"res"=>null,
					"err"=>$err
				)
			);

		} elseif (  is_array($categories) && count($categories)  ){

			// --------------- Вывод категорий ---------------

			$tmp = Array();
			foreach($categories as $cat){
				foreach($cat["attachments"] as $attach){
					if($attach){
						$tmp[] = $attach;
					}
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

			// --------------------------------------------------

			if ( isset($_GET["json"]) && $_GET["json"] ){
				$this->view->render(
					"jsonView.php",
					Array(
						"res" => &$categories,
						"err" => &$categoriesModel->lastError
					)
				);
			} else {
				$this->view->render(
					"page.php",
					Array(
						"_bodyTemplateFile" => rtrim($this->view->viewBasePath,"\\/") . "/" . "categories.php",
						"categories" => &$categories,
						"err" => &$categoriesModel->lastError
					)
				);
			}

			// --------------------------------------------------

		} elseif (
			is_array($categories)
			&& !count($categories)
			&& $id
		){

			// --------------- Вывод товаров в категории ---------------

			$limit = null;

			if ( isset($_GET["page"]) ){
				$page = intval($_GET["page"]);

				if ( isset($_GET["perpage"]) ){
					$perpage = intval($_GET["perpage"]);
				}

				if (!isset($perpage) || $perpage == 0) $perpage = 24;

				if ($page > 0){
					$limit = ["from"=>($page - 1) * $perpage, "amount"=>$perpage];
				} else {
					$limit = ["from"=>0, "amount"=>$perpage];
				}
			}

			$productsModel = new ProductsModel();

			$products = $productsModel->getProducts(
				Array(
					"status"=>"published",
					"category" => $id,
					"limit" => $limit
				)
			);

			// --------------------------------------------------

			$tmp = Array();
			foreach($products as $prod){
				foreach($prod["attachments"] as $attach){
					if($attach){
						$tmp[] = $attach;
					}
				}
			}

			$attachments = $attachmentsModel->getAttachments(Array("id"=>$tmp));
			$attachmentsLnk = Array();

			foreach($attachments as &$tmp){
				$attachmentsLnk[$tmp["id"]] = &$tmp;
			}

			foreach($products as &$tmp){
				foreach($tmp["attachments"] as &$tmp2){
					$tmp2 = $attachmentsLnk[$tmp2];
				}
			}

			// --------------------------------------------------

			if ( isset($_GET["json"]) && $_GET["json"] ){
				$this->view->render(
					"jsonView.php",
					Array(
						"res" => &$products,
						"err" => &$productsModel->lastError
					)
				);
			} else {
				$this->view->render(
					"page.php",
					Array(
						"_bodyTemplateFile" => rtrim($this->view->viewBasePath,"\\/") . "/" . "products.php",
						"products" => &$products,
						"category" => &$categoryModel,
						"err" => &$productsModel->lastError
					)
				);
			}

			// --------------------------------------------------

		}

	}

}