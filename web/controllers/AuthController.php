<?php
Class AuthController extends \Base\Controller {

	public function auth(){
		$auth_res = Array(
			"errors" => Array(),
			"auth" => false
		);

		$auth = new Auth();

		if ( isset($_POST["login"], $_POST["password"]) ) {
			$login = strtolower(str_replace(["\\","/","'",'"'],"",$_POST["login"]));

			$pwd = $_POST["password"];
			$isAuth = $auth->login($login, $pwd);
			$auth_res["auth"] = (!$isAuth ? false : true);
		} else {
			$isAuth = $auth->login();
			$auth_res["auth"] = (!$isAuth ? false : true);
		}

		$this->view->render(
			"jsonView.php",
			Array(
				"res" => $auth_res,
				"err" => null
			)
		);
	}

	public function logout(){
		$core = new \Base\Core();
		$rooturl = $core->get("rootURL");
		$auth = new Auth();
		$auth->logout();
		header("Location: " . $rooturl);
	}

	public function loginPage(){
		$this->view->render(
			"loginView.php",
			Array(
				"err" => null
			)
		);
	}

}