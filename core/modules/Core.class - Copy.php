<?php

Namespace Base;

// Фабрика-СервисЛокатор
Class Core {

	public static $methods = Array();

	function __construct(){
		$this->methods = Array(
			"rootDir" => function(){
				$dir = array_reverse(Utils::m_explode(Array("\\","/"),__DIR__));
				$dir_ = Array();
				for($c=0; $c<count($dir); $c++){
					if ($c < 2) continue;
					array_push($dir_,$dir[$c]);
				}
				return implode("/",array_reverse($dir_));
			},

            "rootURL" => function(){
                $rootdir = Utils::m_explode(Array("\\","/"), trim($this->get("rootDir")," \\/"));
				$req_uri = Utils::m_explode(Array("\\","/"), trim($_SERVER['REQUEST_URI'], "\\/"));
				list($protocol) = explode("/",$_SERVER['SERVER_PROTOCOL']);
				$protocol = strtolower($protocol);
				$servername = $_SERVER['SERVER_NAME'];
				$tmp = Array();

				foreach($req_uri as $u){
					if (!trim($u)) continue;

					foreach($rootdir as $d){
						if ($u == $d){
							array_push($tmp,$u);
							break 2;
						}
					}

					array_push($tmp,$u);
				}

				return $protocol . '://' . $servername . '/' . implode("/",$tmp);
            },
		
			// ------------------------------------------
		
			"db" => function($arg = Array()){
				$factory = new DBFactory();
				return $factory->get($arg);
			},
			
			// ------------------------------------------
			
			"request" => function($arg = Array()){
				return;
			},
			
			// ------------------------------------------
			
			"response" => function($arg = Array()){
				return;
			}
		);
	}

	function get($what = null, $arg = Array()){

		if (!isset($this->methods[$what])) return;

		if ( is_callable($this->methods[$what]) ){
			return call_user_func($this->methods[$what], $arg);
		} elseif (is_string($this->methods[$what])){
			return new $this->methods[$what]($arg);
		}
		
	}
	
	public function set($what = null, $set = null){
	
		if (!$what || !$set) return;
	
		if ( is_callable($set) ){
			$this->methods[$what] = $set;
		} elseif ( is_string($set) ) {
			$this->methods[$what] = $set;
		} elseif ( !$set ){
			unset($this->methods[$what]);
		}
		
	}
	
}