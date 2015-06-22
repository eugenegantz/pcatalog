<?php

class allRequests extends \Base\Controller {
	function action(){

		// Назначение конфигов
		Base\Core::setGlobal(
			"config",
			function(){
				$rootdir = Base\Core::getGlobal("rootDir");

				$config = rtrim($rootdir,"\\/") . "/config/config.json";

				if ( !file_exists($config) ){
					echo "ФАЙЛ НАСТРОЕК НЕ НАЙДЕН";
					return;
				}

				$config = file_get_contents($config);
				$config = json_decode($config, true);

				return $config;
			}
		);

		// -------------------------------------
		// HTTP REFERER

		$core = new \Base\Core();
		$config = $core->get("config");

		$allowedRefDomains = $config["allowedRefDomains"];

		if ( isset($_SERVER["HTTP_REFERER"]) ){
			$ref = parse_url($_SERVER["HTTP_REFERER"]);
			if (!in_array($ref["host"], $allowedRefDomains)){
				exit();
			}
		}

	}
}