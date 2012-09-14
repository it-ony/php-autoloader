#!/bin/env php
<?php

try {
    require __DIR__ . "/../../autoloader.php";

    $a = new ClassA();
    $b = new ClassB();

    var_dump($a, $b);

} catch (ExceptionB $e) {

}
