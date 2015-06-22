<?php

Namespace Base;

class Routes {

	private $routes = Array();

	private $middleRoutes = Array();

	function __construct($arg = null){

		if (!$arg){
			echo "Routes::__construct error\n";
			return null;
		}

		foreach($arg as $tmp){
			if(
				isset(
					$tmp["controller"],
					$tmp["path"],
					$tmp["action"]
				)
			){
				$this->addRoute(
					Array(
						"path" => $tmp["path"],
						"controller" => $tmp["controller"],
						"action" => $tmp["action"],
						"method" => (isset($tmp["method"]) ? $tmp["method"] : null)
					)
				);
			} elseif (isset($tmp["method"],$tmp["controller"],$tmp["action"])) {
				$this->addMiddleRoute(
					Array(
						"method"=>$tmp["method"],
						"controller"=>$tmp["controller"],
						"action"=>$tmp["action"]
					)
				);
			}
		}
	}

	public function addRoute($arg = Array()){

		$path = (isset($arg["path"]) ? $arg["path"] : null );

		$method = (isset($arg["method"]) ? $arg["method"] : null );

		$controller = (isset($arg["controller"]) ? $arg["controller"] : null );

		$action = (isset($arg["action"]) ? $arg["action"] : null );

		// ------------------------------
		// Если не указан паттерн для URL,
		// то выполнить указанную логику для всех путей

		if( !$path && $method && $controller && $action ){
			$this->addMiddleRoute(
				Array(
					"method"=>$method,
					"controller"=>$controller,
					"action"=>$action
				)
			);
			return;
		}

		// ------------------------------

		$path = explode("/",str_replace("\\","/",trim($path," ./\\")));

		$route_ = Array(
			"controller" => $controller,
			"method" => $method,
			"action" => $action,
			"nodes" => Array(),
			"path" => implode("/",$path)
		);

		foreach($path as $key => $tmp){

			$match = Array();

			if(preg_match('/\{([^)]+)\}/', $tmp, $match)){
				$route_["nodes"][$key] = Array("var" => $match[1]);
			} else {
				$route_["nodes"][$key] = $tmp;
			}

		}

		if (
			count($route_['nodes'])
			&& $controller
			&& $action
		){
			$this->routes[] = $route_;
		}
	}

	public function addMiddleRoute($arg){
		if (!isset($arg["method"], $arg["controller"], $arg["action"])){
			return;
		}
		$this->middleRoutes[] = Array(
			"method" => $arg["method"],
			"controller" => $arg["controller"],
			"action" => $arg["action"]
		);
	}

	public function matchRoute($arg = Array()){

		$uri = (isset($arg["uri"]) ? $arg["uri"] : null );

		$method = (isset($arg["method"]) ? $arg["method"] : null );

		// Если не указан URI
		if ($uri === null){
			$return["matched"] = false;
			return $return;
		}

		$uri = explode("/",str_replace("\\","/",trim($uri," ./\\")));

		$returns = Array();

		foreach($this->routes as $route){

			if(
				$method !== null
				&& $route["method"] !== null
				&& strtolower($method) != strtolower($route["method"])
			){
				continue;
			}

			if (!$route["controller"] || !$route["action"]) continue;

			$return = Array(
				"route" => $route,
				"matched" => true,
				"controller" => $route["controller"],
				"action" => $route["action"],
				"vars" => Array()
			);

			foreach($route["nodes"] as $key => $node){
				if(
					isset($uri[$key])
					&& $uri[$key] == $node
				){

				} elseif (
					is_array($node)
					&& isset($node["var"])
					&& isset($uri[$key])
				){
					$return["vars"][$node["var"]] = $uri[$key];
				} else {
					$return["matched"] = false;
				}
			}

			if ($return["matched"]){
				$returns[] = $return;
			}
		}

		usort($returns,function($a,$b){
			$ac = count($a["route"]["nodes"]);
			$bc = count($b["route"]["nodes"]);
			if ( $ac <= $bc ) return -1;
			if ( $ac > $bc ) return 1;
		});

		return end($returns);
	}

	public function matchMiddleRoutes($method){
		$method = strtolower($method);
		$return = Array();
		foreach($this->middleRoutes as $mr){
			if (strtolower($mr["method"]) == strtolower($method) || $mr["method"] == "*"){
				$return[] = Array(
					"method" => $mr["method"],
					"controller" => $mr["controller"],
					"action" => $mr["action"]
				);
			}
		}
		usort($return, Array(__CLASS__,"sortMiddleRoutes"));
		return $return;
	}

	static function sortMiddleRoutes($a,$b){
		if($a["method"] >= $b["method"]) return 1;
		if($a["method"] < $b["method"]) return -1;
	}
}