<?php

Namespace Base;

Class Core extends CoreAbstract {

	static function init(){

		self::$static_methods["rootDir"] = function(){
            $dir = array_reverse(Utils::m_explode(Array("\\","/"),__DIR__));
            $dir_ = Array();
            for($c=0; $c<count($dir); $c++){
                if ($c < 2) continue;
                array_push($dir_,$dir[$c]);
            }
            return implode("/",array_reverse($dir_));
		};

		// -----------------------------------------------------------------

        self::$static_methods["rootURL"] = function(){
            $rootdir = Utils::m_explode(Array("\\","/"), trim(self::getGlobal("rootDir")," \\/"));
			$req_uri = explode("?",$_SERVER['REQUEST_URI']);
            $req_uri = Utils::m_explode(Array("\\","/"), trim($req_uri[0], "\\/"));
			list($protocol) = explode("/",$_SERVER['SERVER_PROTOCOL']);
			$protocol = strtolower($protocol);

            $tmp = array_intersect($rootdir,$req_uri);

            return $protocol . '://' . $_SERVER['SERVER_NAME'] . '/' . implode("/",$tmp);
        };

		// -----------------------------------------------------------------

        self::$static_methods["db"] = function($arg = Array()){
			$factory = new DBFactory();
			return $factory->get($arg);
		};

		// -----------------------------------------------------------------

        self::$static_methods["request"] = "\\Base\\Request";

		// -----------------------------------------------------------------

		self::$static_methods["response"] = function($arg = Array()) {
            return null;
        };

	}

}