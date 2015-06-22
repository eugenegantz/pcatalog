<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 6/13/2015
 * Time: 3:01 PM
 */

class Utils {
	public function parseArgument($arg = Array()){

		$into = ( isset($arg["into"]) ? strtolower($arg["into"]) : "array" );

		$value = ( isset($arg["value"]) ? $arg["value"] : null );

		$kickEmpty = ( isset($arg["kickEmpty"]) ? $arg["kickEmpty"] : true );

		$toLowerCase = ( isset($arg["toLowerCase"]) ? $arg["toLowerCase"] : false );

		$toUpperCase = ( isset($arg["toUpperCase"]) ? $arg["toUpperCase"] : false );

		$toInt = ( isset($arg["toInt"]) ? $arg["toInt"] : false );

		$toFloat = ( isset($arg["toFloat"]) ? $arg["toFloat"] : false );

		$isInt = ( isset($arg["isInt"]) ? $arg["isInt"] : false );

		$isFloat = ( isset($arg["isFloat"]) ? $arg["isFloat"] : false );

		$delimiters = ( isset($arg["delimiters"]) && is_array($arg["delimiters"]) ? $arg["delimiters"] : [";",","] );

		$filters = ( isset($arg["filters"]) && is_array($arg["filters"]) ? $arg["filters"] : null );

		// --------------------------------------------------------------------------------

		if ($value === null) {
			return null;
		}

		if($into == "array"){

			if ( is_array($value) ){

			} elseif ( is_string($value) ) {

				$value = str_replace($delimiters, $delimiters[0], $value);
				$value = explode($delimiters[0],trim($value));

			} elseif ( is_int($value) ) {

				$value = [$value];

			} else {
				return null;
			}

			$tmp = Array();

			foreach($value as $val){

				// Если пустая ячейка
				if(
					$kickEmpty
					&& (
						$val === ""
						|| $val === null
					)
				) continue;

				// Привести в нижний регистр
				if ($toLowerCase && is_string($val)) $val = strtolower($val);

				// Перевести в верхний регистр
				if ($toUpperCase && is_string($val)) $val = strtoupper($val);

				// Откинуть все НЕ цифры
				if ($isInt || $isFloat){
					if (is_string($val) && is_numeric($val)){
						if( $isFloat ){
							$val = str_replace(",",".",$val);
							if (!preg_match('/[.]/',$val)) continue;
						} elseif ( $isInt ){
							$val = str_replace(",",".",$val);
							if (preg_match('/[.]/',$val)) continue;
						}
					} elseif ( $isInt && !is_int($val) ) {
						continue;
					} elseif ( $isFloat && !is_float($val) ){
						continue;
					}
				}

				// Привести к целому
				if ($toInt) {
					$val = intval($val);
				}

				// Привести к десятичной
				if ($toFloat) {
					$val = floatval($val);
				}

				// Применить фильтры
				if (is_array($filters)){
					foreach($filters as $filter){
						$val = filter_var($val, $filter);
					}
				}

				$tmp[] = $val;

			}

			$value = $tmp;

			return $value;

		}

	}


	public function paginationTemplate($page,$perpage,$number){
		$html = Array();

		if ( $number ) {
			$fr = intval($number);
			$pages = ceil($fr / $perpage);

			$p = $page - 3;
			$p2 = $page + 1;
			if ($p < 0){
				$p = 1;
				$p2 = 3;
			}

			if ( $page > 1 ) $html[] = '<li page="prev" page="1">←</li>';
			if ( $page != 1 && $p > 1 ) $html[] = '<li>' . 1 . '</li><span>...</span>';

			for($c=$p; $c <= $p2; $c++){
				if ($c < 1) continue;
				if ($c > $pages) continue;
				if ( $c == $page ) {
					$html[] = '' .
						'<span>&nbsp;</span>' .
						'<input style="width:32px;" type="number" value="' . $page . '">' .
						'<span>&nbsp;</span>';
				} else {
					$html[] = '<li page="'. $c .'">' . $c . '</li>';
				}
			}
			if ( $page != $pages && $p2 < $pages ) $html[] = '<span>...</span><li page="' . $pages . '">' . $pages . '</li>';
			if ( $page < $pages ) $html[] = '<li page="next">→</li>';
		} else {
			$html[] = '<li page="1">1</li>';
		}

		return implode("",$html);
	}
}