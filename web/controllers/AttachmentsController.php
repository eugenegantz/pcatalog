<?php

class AttachmentsController extends \Base\Controller {
	public function getAttachments(){
		header("Content-type: text/plain");

		$utils = new Utils();

		$id = null;

		if ( isset($_GET["id"]) ){
			$id = $utils->parseArgument(
				Array(
					"into"=>"array",
					"isInt"=>true,
					"kickEmpty"=>true,
					"delimiters"=>[";",","],
					"value"=>$_GET["id"]
				)
			);
		}

		$attachmentsModel = new AttachmentsModel();

		$a = $attachmentsModel->getAttachments(Array("id"=>$id));

		echo json_encode($a);
	}

	public function test(){
		require_once("./web/views/attachmentsTest.php");
	}

	public function uploadAttachments(){

		$auth = new Auth();
		if (!$auth->login()) return null;

		$attachmentsModel = new AttachmentsModel();

		$res = $attachmentsModel->upload();

		if ( $attachmentsModel->lastError ){
			echo
			json_encode(
				Array(
					"id" => Array(),
					"errors" => $attachmentsModel->lastError
				)
			);
		} else {
			echo json_encode($res);
		}

	}
}