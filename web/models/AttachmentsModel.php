<?php

class AttachmentsModel {

	public $db, $lastError = null;

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

	public function getAttachments($arg = Array()){

		if ( !isset($this->db) ) {
			$this->lastError = "Undefined ID";
			return null;
		}

		$SQLConditions = [];

		// ---------------------------------------------

		$id = null;

		if ( isset($arg["id"]) ){
			$id = $this->utils->parseArgument(
				Array(
					"into" => "array",
					"isInt" => true,
					"kickEmpty" => true,
					"value" => $arg["id"],
					"delimiters" => [";",","]
				)
			);

			if ( is_array($id) && count($id) ){
				$SQLConditions[] = "id IN(" . implode(',',$id) . ")";
			}
		}



		// ---------------------------------------------

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

		// ---------------------------------------------

		$SQL = [
			"SELECT * FROM attachments",
			(count($SQLConditions) ? ' WHERE ' . implode(' AND ',$SQLConditions) : '' ),
			"ORDER BY attachments.id DESC",
			($limit ? ' LIMIT ' . $limit : '' )
		];

		$dbres = $this->db->query(
			Array(
				"query"=>implode(" ",$SQL)
			)
		);

		if(count($dbres["err"])){
			$this->lastError = "DB Err: " . implode('; ',$dbres["err"]);
			return null;
		}

		return $dbres["rows"];

	}

	public function getNewID(){
		if(!isset($this->db)){
			$this->lastError = "Undefined DB";
			return null;
		}

		$dbres = $this->db->query(
			Array(
				"query" => "SELECT IF(MAX(id) IS NULL,1,MAX(id)+1) as id FROM attachments"
			)
		);

		$id = $dbres["rows"][0]["id"];

		return $id;
	}

	public function upload($files = null, $arg = Array()){

		$inputName = ( isset($arg["inputName"]) && $arg["inputName"] ? $arg["inputName"] : null );

		// Допустимый размер файлов
		$maxFileSize = 2 * 1024 * 1024; // 2 mb

		// Максимальный размер холста по ширине или высоте
		$maxCanvasSize = 256;

		$coreUtils = new \Base\Utils();

		$rootdir = \Base\Core::getGlobal("rootDir");

		$fullSizeURI = "/web/static/uploads/sources/";

		$thumbnailURI = "/web/static/uploads/thumbnails/";

		// Директория для оригиналов
		$fullSizeDir = rtrim($rootdir, "\\/") . $fullSizeURI;

		// Директория для миниатюр
		$thumbnailDir = rtrim($rootdir, "\\/") . $thumbnailURI;

		// Допустимые расширения файлов
		$extensions  = Array("jpg","jpeg","png");

		$return = Array(
			"errors" => Array(),
			"id" => Array()
		);

		// ------------------------------------------------------------

		// Если массив не передан, брать из глобального контекста
		if (!$files){
			if (!count($_FILES)) {
				$this->lastError = "Files is empty";
				return null;
			}
			$files = &$_FILES;
		}

		if ( !$inputName ){

			$tmp = array_keys($files);
			$inputName = $tmp[0];

		} elseif ( isset($files[$inputName]) ){

			$files = &$_FILES;

		} else {
			$this->lastError = "Files is empty";
			return null;
		}

		// ------------------------------------------------------------

		if ( count($files[$inputName]['name']) > 10 ) {
			$this->lastError = "Upload number limit";
			return null;
		}


		foreach ( $files[$inputName]['name'] as $c => $name ) {

			$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

			$name = strtolower($coreUtils->rus2translit($name));

			// Проверка размера.
			if ( $files[$inputName]['size'][$c] >= $maxFileSize ) continue;

			// Проверка расширения
			if( !in_array($ext, $extensions) ) continue;

			// Дать название файлу
			do {
				$newFileName = (time() + mt_rand(1,99)) . "." . $ext;
				$newFilePath = $fullSizeDir . $newFileName;
				$newThumbPath = $thumbnailDir . $newFileName;
			} while (
				file_exists($newFilePath)
				|| file_exists($newThumbPath)
			);

			// запись файла в папку
			if(
				copy(
					$files[$inputName]["tmp_name"][$c],
					$newFilePath
				)
			){
				// Создание миниатюры
				list($originalWidth, $originalHeight) = getimagesize($newFilePath);

				if ( $originalWidth >= $originalHeight ) {
					$ratio = $originalWidth / $maxCanvasSize;
				} else {
					$ratio = $originalHeight / $maxCanvasSize;
				}

				$finalWidth = $originalWidth / $ratio;

				$finalHeight = $originalHeight / $ratio;

				$sizeRatio = $originalWidth / $originalHeight;


				if ( in_array($ext,Array("jpg","jpeg")) ){
					$image_p = imagecreatetruecolor($finalWidth, $finalHeight);
					$image = imagecreatefromjpeg($newFilePath);
					imagecopyresampled($image_p, $image, 0, 0, 0, 0, $finalWidth, $finalHeight, $originalWidth, $originalHeight);
					imagejpeg($image_p, $newThumbPath, 100);
				}


				if ($ext == "png"){
					$image_p = imagecreatetruecolor($finalWidth, $finalHeight);
					imagesavealpha($image_p, true);
					imageAlphaBlending($image_p, true);
					$color = imagecolorallocatealpha($image_p,0x00,0x00,0x00,127);
					imagefill($image_p, 0, 0, $color);
					$image = imagecreatefrompng($newFilePath);
					imageAlphaBlending($image, true);
					imageSaveAlpha($image, true);
					imagecopyresampled($image_p, $image, 0, 0, 0, 0, $finalWidth, $finalHeight, $originalWidth, $originalHeight);
					imagepng($image_p, $newThumbPath, 0);
				}

				// --------------------- Запись в БД ---------------------

				$id = $this->getNewID();

				$return["id"][] = $id;

				$SQL = [
					"INSERT INTO attachments",
					"(id, path, thumbnail, type)",
					"VALUES (" . $id . ", '" . $fullSizeURI . $newFileName . "', '" . $thumbnailURI . $newFileName . "', 'image')"
				];

				$dbres = $this->db->query(
					Array(
						"query" => implode(" ",$SQL)
					)
				);

			} // close.copy;thumbnail

		} // close.foreach;

		// --------------------- Ответ ---------------------

		return $return;

	}

}