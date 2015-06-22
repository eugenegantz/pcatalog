<?php

class Auth {

	static $instances = Array();

	public $db;

	public $lastError = null;

	public $id, $email, $group, $isAuth = false;

	function __construct($arg = Array()){
		if(isset($arg["db"])){
			$this->db = $arg["db"];
		} else {
			$config = \Base\Core::getGlobal("config");
			$this->db = \Base\Core::getGlobal(
				"db",
				Array(
					"dbtype" => "mysql",
					"dbaddress" => $config["db_address"],
					"dbname" => $config["db_name"],
					"dblogin" => $config["db_user"],
					"dbpassword" => $config["db_password"],
					"logfile" => $config["db_logfile"]
				)
			);
			$this->db = $this->db[0];
		}

		self::$instances[] = $this;
	}

	public function logout(){
		if ( session_status() != PHP_SESSION_ACTIVE ){
			session_start();
		}

		session_unset();
		session_destroy();
		$_SESSION['userID'] = null;

		$this->isAuth = false;
		$this->id = null;
		$this->email = null;
		$this->group = null;
	}

	public function login($login = null, $pwd = null){

		if ( isset($login, $pwd) ){

			$login = strtolower(str_replace(["\\","/","'",'"'],"",$login));
			$login = filter_var($login, FILTER_SANITIZE_MAGIC_QUOTES);
			$login = filter_var($login, FILTER_SANITIZE_STRING);

			$SQL = "
			SELECT id, users.group, hash, salt, email
			FROM users
			WHERE email = '" . $login . "';
			";

			$dbres = $this->db->query(Array("query" => $SQL));

			if ( !$dbres["num_rows"] ) {
				$this->lastError = "Wrong login";
				return null;
			}

			$dbrow = $dbres["rows"][0];

			$pepper = "zwerty";

			$salt = $dbrow["salt"];

			$hash = sha1($pwd . $salt . $pepper);

			if ( $dbrow["hash"] != $hash ) {
				$this->logout();
				$this->lastError = "Wrong password";
				return null;
			} else {
				session_start();
				$this->isAuth = true;
				$this->email = $dbrow["email"];
				$this->group = $dbrow["group"];
				$this->id = $dbrow["id"];
				$_SESSION["userID"] = $this->id;
				return true;
			}

		} else {

			if (!strlen(session_id())) {
				session_start();
			}

			if ( !isset($_SESSION['userID']) || $_SESSION['userID'] === null ){
				$this->logout();
				return null;
			}

			$SQL = "
			SELECT id, email, users.group
			FROM users
			WHERE id = " . $_SESSION['userID'] . ";
			";

			$dbres = $this->db->query(Array("query" => $SQL));

			if (!$dbres["num_rows"]){
				$this->lastError = "User not exist";
				$this->logout();
				return null;
			}

			$dbrow = $dbres["rows"][0];

			$this->isAuth = true;
			$this->email = $dbrow["email"];
			$this->group = $dbrow["group"];
			$this->id = $dbrow["id"];
			return true;
		}
	}

}