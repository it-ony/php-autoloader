<?php

echo "ClassConstructorTrait loaded.\n";

trait ClassConstructorTrait
{
    
    static public function classConstructor()
    {
        echo __CLASS__, " loaded.\n";
    }
    
}