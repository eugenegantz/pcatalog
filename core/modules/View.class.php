<?php

Namespace Base;

Class View {

	public $viewBasePath = './web/views';

	public $viewpath = null;

	public $data;

	public function render($viewpath = null, $data = Array()){

		if( isset($data) ){
			$this->data = $data;
		}

		if ($viewpath){
			$this->viewpath = $viewpath;
		}

		if($this->viewpath){
			$filepath = rtrim($this->viewBasePath," \\/") . "/" . ltrim($this->viewpath, " \\/");

			if ( file_exists($filepath) ){
				include_once($filepath);
			} else {
				echo '{"err":"Unable to load \"view\" "}';
			}

		}

	}

}