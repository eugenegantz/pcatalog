<?php 
spl_autoload_register( function ($classname){
	$modulesDir = __DIR__ . "/modules/";
	$classname = explode("/",str_replace(["\\","/"], "/", $classname));
	$classname = end($classname);
	$filename = $modulesDir . $classname .".class.php";
	if ( file_exists($filename) ){
		include_once($filename);
	}
} );

spl_autoload_register( function ($classname){
	$modulesDir = __DIR__ . "/../web/controllers/";
	$filename = $modulesDir . $classname .".php";
	if ( file_exists($filename) ){
		include_once($filename);
	}
} );

spl_autoload_register( function ($classname){
	$modulesDir = __DIR__ . "/../web/models/";
	$filename = $modulesDir . $classname .".php";
	if ( file_exists($filename) ){
		include_once($filename);
	}
} );