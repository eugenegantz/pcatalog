<?php

Namespace Base;

Class Utils {
	static function m_explode($d, $s){
		return explode($d[0],str_replace($d,$d[0],$s));
	}

	static function hasSQLWords($str){
		$str = strtolower($str);
		if (preg_match("/select|update|delete|insert|join|where/",$str)){
			return true;
		}
		return false;
	}

	static function rus2translit($s){
		$lib = Array(
			'а' => 'a',		'б' => 'b',		'в' => 'v',
			'г' => 'g',		'д' => 'd',		'е' => 'e',
			'ё' => 'e',		'ж' => 'zh',	'з' => 'z',
			'и' => 'i',		'й' => 'y',		'к' => 'k',
			'л' => 'l',		'м' => 'm',		'н' => 'n',
			'о' => 'o',		'п' => 'p',		'р' => 'r',
			'с' => 's',		'т' => 't',		'у' => 'u',
			'ф' => 'f',		'х' => 'h',		'ц' => 'c',
			'ч' => 'ch',	'ш' => 'sh',	'щ' => 'sch',
			'ь' => '',		'ы' => 'y',		'ъ' => '',
			'э' => 'e',		'ю' => 'yu',	'я' => 'ya',
			'А' => 'A',		'Б' => 'B',		'В' => 'V',
			'Г' => 'G',		'Д' => 'D',		'Е' => 'E',
			'Ё' => 'E',		'Ж' => 'Zh',	'З' => 'Z',
			'И' => 'I',		'Й' => 'Y',		'К' => 'K',
			'Л' => 'L',		'М' => 'M',		'Н' => 'N',
			'О' => 'O',		'П' => 'P',		'Р' => 'R',
			'С' => 'S',		'Т' => 'T',		'У' => 'U',
			'Ф' => 'F',		'Х' => 'H',		'Ц' => 'C',
			'Ч' => 'Ch',	'Ш' => 'Sh',	'Щ' => 'Sch',
			'Ь' => '',		'Ы' => 'Y',		'Ъ' => '',
			'Э' => 'E',		'Ю' => 'Yu',	'Я' => 'Ya',
			'(' => '_', 	')' => '_', 	'[' => '_',
			']' => '_',		' ' => '_'
		);
		return strtr($s, $lib);
	}

}
