<?php

class ExceptionB extends Exception {
	
	
	static public function __static() {
		echo __CLASS__, " loaded.\n";
	}
	
	
}