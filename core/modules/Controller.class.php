<?php
Namespace Base;

Class Controller {

	private $next = null;

	public $action = null;

	public $arguments = Array();

	public $view;

	public function setNext($next){
		$this->next = $next;
	}

	public function getNext(){
		if($this->next){
			return $this->next;
		}
		return null;
	}

    function __construct($arg = Array()){

		$this->arg = $arg;

        if (
            isset($arg["view"])
            && get_class($arg["view"]) == "View"
        ){
            $this->view = $arg["view"];
        } else {
            $this->view = new View();
        }

    }

	public function proceed(){

		$action = $this->action;
		$this->$action($this->arguments);

		if($next = $this->getNext()) {
			$next->arguments = $this->arguments;
			$next->proceed();
		}

	}

}