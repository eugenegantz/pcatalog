<?php

Namespace Base;


class DataValidator {

	/* TODO
	 * required 1
	 * isInteger 2
	 * isFloat 3
	 * isString 4
	 * isArray 5
	 * toInteger
	 * toFloat
	 * trim
	 * toLowerCase
	 * toUpperCase
	 * array:kickEmpty
	 * isEmpty 6
	 * string,array,numeric:maxLenght 10
	 * string,array,numeric:minLength 11
	 * numeric:min 12
	 * numeric:max 13
	 * numeric,string:equal 16
	 * numeric:range 17
	 * array,string:contain 18
	*/

	private $conditions = Array();

	private $rules = Array();

	function __construct($conditions){

		$this->rules = Array(

			/*
			 * Поле является обязательным
			 * */
			"required" => Array(
				"code" => 1,
				"fx" => function($value, $cond){
					// Поле необходимо
					if ( $cond === true && isset($value) ) return true;
					// В данном поле нет необходимости
					if ( $cond === false && !isset($value) ) return true;
					return false;
				}
			),

			"isInteger" => Array(
				"code" => 2,
				"fx" => function($value = null, $cond = null){
					if ( is_string($value) ){
						$value = str_replace(',','.',$value);
						if ( is_numeric($value) && !preg_match('/[.]/', $value) && $cond === true ) return true;
						if ( is_numeric($value) && preg_match('/[.]/', $value) && $cond === false ) return true;
					} elseif ( is_integer($value) ) {
						// Все целые
						if ($cond === true) return true;
						// Все кроме целых
						if ($cond === false) return false;
					}
					return false;
				}
			),

			"isFloat" => Array(
				"code" => 3,
				"fx" => function($value = null, $cond = null){
					if ( is_string($value) ){
						$value = str_replace(',','.',$value);
						if ( is_numeric($value) && preg_match('/[.]/', $value) && $cond === true ) return true;
						if ( is_numeric($value) && !preg_match('/[.]/', $value) && $cond === false ) return true;
					} elseif ( is_float($value) ) {
						if ($cond === true) return true;
						if ($cond === false) return false;
					}
					return false;
				}
			),

			"isString" => Array(
				"code" => 4,
				"fx" => function($value = null, $cond = null){
					// Все строки
					if(is_string($value) && $cond === true) return true;
					// Все кроме строк
					if(!is_string($value) && $cond === false) return true;
					return false;
				}
			),

			"isEmpty" => Array(
				"code" => 6,
				"fx" => function($value = null, $cond = null){
					if ($value === null && $cond === true) return true;

					if (is_string($value) && !$value && $cond === true) return true;
					if (is_string($value) && $value && $cond === false) return true;

					if (is_array($value) && count($value) && $cond === true) return true;
					if (is_array($value) && !count($value) && $cond === false) return true;

					if ($value) return true;

					return false;
				}
			),

			"isArray" => Array(
				"code" => 5,
				"fx" => function($value = null, $cond = null){
					if (is_array($value) && $cond === true) return true;
					if (!is_array($value) && $cond === false) return true;
					return false;
				}
			),

			/*
			 * Допустимые мин. и макс длины строк
			 * */
			"string:maxLength" => Array(
				"code" => 10,
				"fx" => function($value = null, $cond = null){
					if (!is_integer($cond)) return false;
					if ( is_string($value) ) {
						if (strlen($value) <= $cond) return true;
					} elseif ( is_array($value) ) {
						foreach($value as $val){
							if (strlen($val) > $cond) return false;
						}
						return true;
					} elseif ( is_numeric($value) ) {
						$value = $value . "";
						if ( strlen($value) <= $cond ) return true;
					}
					return false;
				}
			),

			"string:minLength" => Array(
				"code" => 11,
				"fx" => function($value = null, $cond = null){
					if (!is_integer($cond)) return false;
					if ( is_string($value) ) {
						if (strlen($value) >= $cond) return true;
					} elseif ( is_array($value) ) {
						foreach($value as $val){
							if (strlen($val) < $cond) return false;
						}
						return true;
					} elseif ( is_numeric($value) ) {
						$value = $value . "";
						if ( strlen($value) >= $cond ) return true;
					}
					return false;
				}
			),

			/*
			 * Допустимые мин. и макс длины массивов
			 * */
			"array:maxLength" => Array(
				"code" => 12,
				"fx" => function($value = null, $cond = null){
					if (!is_integer($cond)) return false;
					if ( is_array($value) && count($value) <= $cond ) return true;
					return false;
				}
			),

			"array:minLength" => Array(
				"code" => 12,
				"fx" => function($value = null, $cond = null){
					if (!is_integer($cond)) return false;
					if ( is_array($value) && count($value) >= $cond ) return true;
					return false;
				}
			),

			/*
			 * Число: Число не меньше чем минимум
			 * Массив: Числа в массиве не меньше чем минимум
			 * То же с максимумом
			 * */
			"numeric:min" => Array(
				"code" => 13,
				"fx" => function($value = null, $cond = null){
					if (!is_integer($cond)) return false;
					if ( is_numeric($value) ) {
						$value = $value * 1;
						if ( $value >= $cond ) return true;
					} elseif ( is_array($value) ) {
						foreach($value as $val){
							if ($val < $cond) return false;
						}
						return true;
					}
					return false;
				}
			),

			"numeric:max" => Array(
				"code" => 14,
				"fx" => function($value = null, $cond = null){
					if (!is_integer($cond)) return false;
					if ( is_numeric($value) ) {
						$value = $value * 1;
						if ( $value <= $cond ) return true;
					} elseif ( is_array($value) ) {
						foreach($value as $val){
							if ($val > $cond) return false;
						}
						return true;
					}
					return false;
				}
			),

			/*
			 * Число: Входит ли число в диапазон,
			 * Массив: входят ли числа в массиве в диапазон
			 * */
			"numeric:range" => Array(
				"code" => 15,
				"fx" => function($value = null, $cond = null){
					if (is_string($cond)){
						$cond = str_replace([";",","," "],";",$cond);
						$cond = explode(";",$cond);
						if (count($cond) == 2){
							$from = $cond[0];
							$to = $cond[1];
						} else {
							return false;
						}
					} elseif ( is_array($cond) && isset($cond["from"], $cond["to"]) ) {
						$from = $cond["from"];
						$to= $cond["to"];
					} else {
						return false;
					}

					if (is_numeric($value)){
						$value = ($value * 1);
						if ( $cond["from"] <= $value && $value <= $cond["to"] ){
							return true;
						}
					} else if ( is_array($value) ){
						$max = null; $min = null;
						foreach($value as $val){
							if ( !is_numeric($val) ) return false;
							$val = $val * 1;
							if ($max === null || $val > $max) $max = $val;
							if ($min === null || $val < $min) $min = $val;
						}
						if ( $from <= $min && $max <= $to ) return true;
					}
					return false;
				}
			),

			"string:contain" => Array(
				"code" => 16,
				"fx" => function($value = null, $cond = null){
					if (!$cond) return false;
					if (is_array($cond) && count($cond)){

					} elseif ( is_string($cond) && strlen($cond) ) {
						$cond = Array($cond);
					} else {
						return false;
					}

					if (is_string($value)){
						if (preg_match('/'. implode("|",$cond) .'/',$value)) return true;
					}

					return false;
				}
			),

			"array:contain" => Array(
				"code" => 17,
				"fx" => function($value = null, $cond = null){
					if (!$cond) return false;
					if (is_array($cond) && count($cond)){

					} elseif ( is_string($cond) && strlen($cond) ) {
						$cond = Array($cond);
					} else {
						return false;
					}

					if (is_array($value)){
						foreach($value as $val){
							if(in_array($val, $cond)){
								return true;
							}
						}
					}

					return false;
				}
			),

			/*
			 * Строка: двухстор. trim для строки
			 * Массив: двухстор. trim для всех строк в массиве
			 * */
			"trim" => Array(
				"type" => "filter",
				"fx" => function($value = null, $cond = null){
					if (!$cond) $cond = " ";
					if (is_array($cond)){
						$cond = implode("",$cond);
					} elseif ( is_string($cond) ) {

					} else {
						return $value;
					}

					if (is_array($value)){
						foreach($value as &$val){
							if (!is_string($val)) continue;
							$val = trim($val, $cond);
						}
					} elseif ( is_string($value) ) {
						$value = trim($value, $cond);
					}

					return $value;
				}
			),

			"array:kickEmpty" => Array(
				"type" => "filter",
				"fx" => function($value = null, $cond = null){
					$tmp = Array();
					if (is_array($value)){
						foreach($value as $key => $val){
							if ( $val === null ){
								continue;
							} elseif (is_string($val) && !$val) {
								continue;
							} elseif ( is_array($val) && !count($val) ) {
								continue;
							}
							$tmp[$key] = $val;
						}
					}
					return $tmp;
				}
			),

			"toLowerCase" => Array(
				"type" => "filter",
				"fx" => function($value = null, $cond = null){
					if (!$value) return $value;
					if (is_string($value)){
						$encoding = mb_detect_encoding($value);
						return mb_strtolower($value, $encoding);
					} elseif ( is_array($value) ) {
						foreach($value as &$val){
							$encoding = mb_detect_encoding($val);
							$val = mb_strtolower($val, $encoding);
						}
						return $value;
					}
					return $value;
				}
			),

			"toUpperCase" => Array(
				"type" => "filter",
				"fx" => function($value = null, $cond = null){
					if (!$value) return $value;
					if (is_string($value)){
						$encoding = mb_detect_encoding($value);
						return mb_strtoupper($value, $encoding);
					} elseif ( is_array($value) ) {
						foreach($value as &$val){
							$encoding = mb_detect_encoding($val);
							$val = mb_strtoupper($val, $encoding);
						}
						return $value;
					}
					return $value;
				}
			)
		);

		// -----------------------------------------

		if ( isset($conditions) ){
			$this->conditions = $conditions;
		}

	}

	public function setCondition($field,$cond){
		// TODO
	}

	/*
	 * Arguments: string $name, array $rule;
	 * Example: $rule = Array("type"=>"rule", "code"=>34, "fx" => function(){});
	 * Type: rule, filter
	 * */
	public function setRule ($name, $rule){
		if ($name && is_array($rule) && is_callable($rule["fx"])) {
			$this->rules[$name] = $rule;
		}
	}

	public function validate($data = null){

		$return = Array();

		if (!is_array($this->conditions)) return null;
		if (!is_array($data)) return null;

		foreach($this->conditions as $fieldName => $fieldCond){

			foreach($fieldCond as $ruleName => $cond){

				if (  isset($this->rules[$ruleName])  ){

					if ( isset($this->rules[$ruleName]["type"]) ){
						$type = $this->rules[$ruleName]["type"];
					} else {
						$type = "rule";
					}

					$value = ( isset($data[$fieldName]) ? $data[$fieldName] : null );

					if ($type == "filter"){

						if (isset($data[$fieldName])){
							$data[$fieldName] = $this->rules[$ruleName]["fx"]($value, $cond);
						}

					} elseif ($type == "rule") {

						$condres = $this->rules[$ruleName]["fx"]($value, $cond);

						if (!$condres) {

							if (!isset($return[$fieldName])) {
								$return[$fieldName] = Array();
							}

							$return[$fieldName][] = $this->rules[$ruleName]["code"];

						}

					}

				}

			}

		}

		return $return;

	}
}