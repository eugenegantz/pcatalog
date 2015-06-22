<?php

Namespace Base;

Class DBLog {
	
	private $logs = Array();

	public function add($str = ""){
		array_push($this->logs, $str);
	}
	
	function __construct($A = Array()){
		$this->logfile = (isset($A['logfile']) ? $A['logfile'] : null );
	}
	
	function __destruct () {
		// Запись логов
		if ( $this->logfile ){

			foreach($this->logs as &$tmp){
				$tmp = str_replace(["\n", "\r", "\t"]," ",$tmp);
			}

			$logstr = implode("\n\n",$this->logs) . "\n\n";

			if (file_exists($this->logfile)){
				if (is_writable($this->logfile)) {
					if (!$file = fopen($this->logfile, 'a')) {
						 echo 'Не удалось открыть файл (' . $this->logfile . ')';
					}

					if (fwrite($file, $logstr) === false) {
						echo 'Не удалось записать файл (' . $this->logfile . ')';
					}
					
					fclose($file);
				} else {
					echo "The file $this->logfile is not writable";
				}
			} else {
				$path = pathinfo($this->logfile, PATHINFO_DIRNAME);
				if ( file_exists($path) ){
					file_put_contents($this->logfile, $logstr);
				}
			}
		}
	}
	
}