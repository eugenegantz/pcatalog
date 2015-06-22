<?php

namespace Base;


class FrontController {

	private $controller;

	private $action;

	private $arguments = Array();

	function __construct($arg = Array()){
		if( isset($arg["routes"]) ) {

			// Объект класса Routes
			$this->routes = $routes = &$arg["routes"];

			$method = $_SERVER["REQUEST_METHOD"];
			list($protocol) = explode("/", $_SERVER['SERVER_PROTOCOL']);
			$protocol = strtolower($protocol);
			$rootURL = str_replace("\\", "/", Core::getGlobal("rootURL"));
			$uri = explode("?",$_SERVER['REQUEST_URI']);
			$url = str_replace("\\", "/", $protocol . '://' . $_SERVER['SERVER_NAME'] . '/' . trim($uri[0], " .\\/"));
			$uri = str_replace($rootURL, "", $url);

			$route = $routes->matchRoute(
				Array(
					"uri" => $uri,
					"method" => $method
				)
			);

			if ($route === null) {
				echo "route === null";
			} elseif (
				isset(
					$route["controller"],
					$route["action"],
					$route["vars"]
				)
			){
				$this->setController($route["controller"]);
				$this->setAction($route["action"]);
				$this->setArguments($route["vars"]);
			}

		} elseif (
			isset(
				$arg["controller"],
				$arg["action"],
				$arg["arguments"]
			)
		){

			if(isset($arg["controller"]) && $arg["controller"]){
				$this->setController($arg["controller"]);
			}
			if(isset($arg["action"]) && $arg["action"]){
				$this->setAction($arg["action"]);
			}
			if(isset($arg["arguments"]) && $arg["arguments"]){
				$this->setArguments($arg["arguments"]);
			}

		}
	}

	function setController($controller = ""){
		$controller = trim($controller);
		if(!class_exists($controller)) return;
		$this->controller = $controller;
	}

	function setAction($action = ""){
		$action = trim($action);
		if(!method_exists($this->controller, $action)) return;
		$this->action = $action;
	}

	function setArguments($arg = Array()){
		$this->arguments = $arg;
	}

	function proceed(){
		if(isset($this->controller, $this->action)){

			$method = $method = $_SERVER["REQUEST_METHOD"];

			if (!class_exists($this->controller)){
				echo "Class " . $this->controller . " don't exist";
				return;
			}

			$controller = new $this->controller();
			$controller->arguments = $this->arguments;
			$controller->action = $this->action;

			$mr = $this->routes->matchMiddleRoutes($method);

			if (count($mr)){
				// Цепь из контроллеров.
				// К примеру, для перехвата значений arguments;

				$chain = Array();

				foreach($mr as $mr_){
					$cn = new $mr_["controller"]();
					$cn->arguments = $this->arguments;
					$cn->action = $mr_["action"];
					$chain[] = $cn;
				}

				$chain[] = $controller;

				for($c=0; $c < count($chain); $c++){
					if( isset($chain[$c],$chain[$c+1]) ){
						$chain[$c]->setNext($chain[$c+1]);
					}
				}

				$chain[0]->proceed();

			} else {
				$controller->proceed();
			}

		}
	}
}