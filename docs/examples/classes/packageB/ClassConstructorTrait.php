<?php

echo "ClassConstructorTrait loaded.\n";

trait ClassConstructorTrait
{
    
    static public function classConstructor()
    {
        echo "calling class constructor for class ", __CLASS__, ".\n";
    }
    
}