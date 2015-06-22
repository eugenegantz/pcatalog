<?php

Namespace Base;

Class DBFactory {
	public function get($A){
		if (  
			!isset($A['dbtype']) 
			|| !trim($A['dbtype']) 
		){
			return null;
		}
		
		$dbtype = $A['dbtype'];

		$new = ( isset($A['new']) ? $A['new'] : false );

		if ($dbtype == "mysql"){
			$B = $A;
			
			unset($B['dbtype'], $B['new']);

			if ( $new ) return Array(new DBMySQL($B));
			
			$res = Array();
			
			foreach(DB::$instances as $instance){
				if ($instance->dbtype != "mysql") continue;
				array_push($res, $instance);
			}
			
			if ( !count($res) ) array_push($res,new DBMySQL($B));
			
			return $res;
		}
	}
}