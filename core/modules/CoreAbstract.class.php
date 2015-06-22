<?php

Namespace Base;

Abstract Class CoreAbstract {

    static $static_methods = Array();

    public $methods = Array();

    // ------------------------------------------------------------------------------------

    static function getGlobal($method = null, $arg = Array()){

		if( !static::hasStaticMethods() ){
			static::init();
		}

        if ( !isset(static::$static_methods[$method]) ) return null;

        if ( is_callable(static::$static_methods[$method]) ){
            return call_user_func(static::$static_methods[$method], $arg);
        } elseif (is_string(static::$static_methods[$method])){
            return new static::$static_methods[$method]($arg);
        }

    }

    function get($method = null, $arg = Array()){

        if (!isset($this->methods[$method])){
            return static::getGlobal($method, $arg);
        }

        if ( is_callable($this->methods[$method]) ){
            return call_user_func($this->methods[$method], $arg);
        } elseif (is_string($this->methods[$method])){
            return new $this->methods[$method]($arg);
        }

    }

    static function setGlobal($method = null, $set = null){

        if (!$method || !$set) return null;

        static::$static_methods[$method] = $set;

    }

    function set($method = null, $set = null){

        if (!$method || !$set) return;

        if ( is_callable($set) ){
            $this->methods[$method] = $set;
        } elseif ( is_string($set) ) {
            $this->methods[$method] = $set;
        } elseif ( !$set ){
            unset($this->methods[$method]);
        }

    }

    function has($method){

        if ( isset($this->methods[$method]) ){
            return true;
        } else {
            return false;
        }

    }

    static function hasGlobal($method){

		if( !static::hasStaticMethods() ) static::init();

        if ( isset(static::$static_methods[$method]) ){
            return true;
        } else {
            return false;
        }

    }

	static function hasStaticMethods(){
		if ( count(static::$static_methods) ) return true;
		return false;
	}

	static function init(){

	}

}