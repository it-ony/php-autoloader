#!/usr/bin/php
<?php

try {
	require_once dirname(__FILE__) . '/../Autoloader.php';
	
	$a = new ClassA();
	$b = new ClassB();

    var_dump($a, $b);
    
} catch (ExceptionB $e) {
	
}