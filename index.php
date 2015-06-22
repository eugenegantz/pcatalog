<?php
$_config_ =  "./config/config.json";

if ( !file_exists($_config_) ) exit('!file_exists('. $_config_ .')');

$_config_ = file_get_contents($_config_);

$_config_ = json_decode($_config_,true);

// ------------------------------------------------------------------------

$_routes_ =  "./config/routes.json";

if ( !file_exists($_routes_) ) exit('!file_exists('. $_routes_ .')');

$_routes_ = file_get_contents($_routes_);

$_routes_ = json_decode($_routes_,true);

// ------------------------------------------------------------------------

require_once(__DIR__ . "/core/init.php");

// ------------------------------------------------------------------------

$frontController = new Base\FrontController(
	Array(
		"routes" => new \Base\Routes($_routes_)
	)
);
$frontController->proceed();

//require_once(__DIR__ . "/test2.php");