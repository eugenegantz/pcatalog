<?php
Class ProductController extends \Base\Controller {

	public function getProduct(){

		$id = $this->arguments["id"];

		$err = null;

		$productsModel = new ProductsModel();

		$attachmentsModel = new AttachmentsModel();

		$product = $productsModel->getProducts(Array("id"=>$id, "output"=>"class-object"));

		if ( count($product) ){
			$product = $product[0];
		} else {
			$err = "Product does not exist";
		}

		if ( $err ){

			$this->view->render(
				"jsonView.php",
				Array(
					"res" => $err,
					"err" => $err
				)
			);

		} else {

			$tmp = Array();
			$productAttachments = $product->getValue("attachment");
			foreach($productAttachments as $attach){
				if($attach) $tmp[] = $attach;
			}

			$attachments = $attachmentsModel->getAttachments(Array("id"=>$tmp));
			$attachmentsLnk = Array();

			foreach($attachments as &$tmp){
				$attachmentsLnk[$tmp["id"]] = &$tmp;
			}

			foreach($productAttachments as &$tmp){
				if (!$tmp) continue;
				$tmp = $attachmentsLnk[$tmp];
			}

			$product->setValue("attachment",$productAttachments);

			// --------------------------------------------------

			if ( isset($_GET["json"]) && $_GET["json"] ) {

				$this->view->render(
					"jsonView.php",
					Array(
						"res" => Array(
							"name" => $product->getValue("name"),
							"id" => $product->getValue("id"),
							"amount" => $product->getValue("amount"),
							"category" => $product->getValue("category"),
							"attachment" => $product->getValue("attachment")
						),
						"err" => $err
					)
				);

			} else {

				$this->view->render(
					"page.php",
					Array(
						"_bodyTemplateFile" => rtrim($this->view->viewBasePath,"\\/") . "/" . "product.php",
						"product" => &$product,
						"err" => $product->lastError
					)
				);

			}

		}

	}

}