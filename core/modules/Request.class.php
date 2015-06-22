<?php
namespace Base;

Class Request {

	public function getPUT() {
		$reqMethod = $_SERVER["REQUEST_METHOD"];
		if (strtolower($reqMethod) == "put"){
			$put = Array();
			parse_str(
				urldecode(
					file_get_contents("php://input")
				),
				$put
			);
			return $put;
		}
		return null;
	}

	public function getDELETE() {
		$reqMethod = $_SERVER["REQUEST_METHOD"];
		if (strtolower($reqMethod) == "delete"){
			$delete = Array();
			parse_str(
				urldecode(
					file_get_contents("php://input")
				),
				$delete
			);
			return $delete;
		}
		return null;
	}

}