<?php


class ExceptionB extends Exception {
	
	
	static public function __static() {
		echo "I'm loaded.\n";
	}
	
	
}