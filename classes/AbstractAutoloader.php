<?php
#########################################################################
# Copyright (C) 2010  Markus Malkusch <markus@malkusch.de>              #
#                                                                       #
# This program is free software: you can redistribute it and/or modify  #
# it under the terms of the GNU General Public License as published by  #
# the Free Software Foundation, either version 3 of the License, or     #
# (at your option) any later version.                                   #
#                                                                       #
# This program is distributed in the hope that it will be useful,       #
# but WITHOUT ANY WARRANTY; without even the implied warranty of        #
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         #
# GNU General Public License for more details.                          #
#                                                                       #
# You should have received a copy of the GNU General Public License     #
# along with this program.  If not, see <http://www.gnu.org/licenses/>. #
#########################################################################


require_once dirname(__FILE__).'/exception/AutoloaderException.php';
require_once dirname(__FILE__).'/exception/AutoloaderException_Include.php';
require_once dirname(__FILE__).'/exception/AutoloaderException_Include_FileNotExists.php';
require_once dirname(__FILE__).'/exception/AutoloaderException_Include_ClassNotDefined.php';


/**
 * An abstract Autoloader 
 *  
 * @package autoloader
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 * @version 1.0
 */
abstract class AbstractAutoloader {

	
	/**
	 * The name of the class constructor is __static.
	 */
    const CLASS_CONSTRUCTOR = '__static';

    
	/**
     * @param String $class
     * @throws AutoloaderException
     */
    abstract protected function __autoload($class);

    
	/**
     * @param String $class
     */
    static public function normalizeClass(& $class) {
        $class = strtolower($class);
    }
        
        
    /**
     * This Autoloader will be registered at the stack.
     * 
     * After registration, this Autoloader is autoloading class definitions.
     * {@link spl_autoload_register()} disables __autoload(). This might be
     * unwanted, so register() also adds __autoload() to the stack.
     * 
     * @see spl_autoload_register()
     */
    public function register() {
        // spl_autoload_register() disables __autoload(). This might be unwanted.
        if (function_exists('__autoload')) {
            spl_autoload_register("__autoload");
        
        }
    	spl_autoload_register($this->getCallback());
    }
    
    
    /**
     * Returns true for Autoloaders in the spl_autoload stack.
     * 
     * @return bool
     */
    public function isRegistered() {
        return in_array($this->getCallback(), spl_autoload_functions(), true);
    }

    
	/**
     * This Autoloader will be removed from the stack.
     * 
     * @see removeAll()
     */
    public function remove() {
    	spl_autoload_unregister($this->getCallback());
    }
    
    
    /**
     * All instances of Autoloader will be removed from the stack.
     * 
     * @see remove()
     */
    static public function removeAll() {
    	foreach (self::getRegisteredAutoloaders() as $autoloader) { //TODO use __CLASS__ in PHP 5.3 and remove the other implementations
    		$autoloader->remove();
    		
    	}
    }
    
    
    /**
     * @return Array all registered Autoloader instances which are doing their jobs
     * @see register()
     */
    static public function getRegisteredAutoloaders() {
    	$autoloaders = array();
        foreach (spl_autoload_functions() as $callback) {
            if (! is_array($callback)) {
                continue;
                
            }
            if (! $callback[0] instanceof self) { //TODO use __CLASS__ in PHP 5.3 and remove the other implementations
                continue;
                
            }
           $autoloaders[] = $callback[0];
            
        }
        return $autoloaders;
    }
    
    
    /**
     * PHP will call this method for loading a class.
     * 
     * If this Autoloader doesn't find a class defintion it will
     * only raise an error if it is the last Autoloader in the stack.
     * 
     * @see handleErrors()
     * @param String $class
     */
    public function autoload($class) {
        self::normalizeClass($class);

    	/*
         * spl_autoload_call() runs the complete stack,
         * even though the class is already defined by
         * a previously registered method.
         */
        if (class_exists($class, false)) {
            return;
        
        }
        

        try {
        	$this->__autoload($class);
            
        } catch (AutoloaderException $exception) {
            if (! $this->handleErrors()) {
                return;
                
            }
            throw $exception;
            
        }
    }
    
    
	/**
     * @return bool If this autoloader is the last in the stack
     */
    private function handleErrors() {
        return array_search($this->getCallback(), spl_autoload_functions())
           === count(spl_autoload_functions()) - 1;
    }
   

    /**
     * @return Callback
     */
    protected function getCallback() {
    	return array($this, 'autoload');
    }
    
    
    /**
     * Includes the class definition and calls the class constructor.
     * 
     * @param String $class
     * @param String $path
     * @throws AutoloaderException_Include
     * @throws AutoloaderException_Include_FileNotExists
     * @throws AutoloaderException_Include_ClassNotDefined
     */
    protected function loadClass($class, $path) {
        if (! @include_once $path) {
            if (! file_exists($path)) {
                throw new AutoloaderException_Include_FileNotExists($path);
                
            } else {
            	$error = error_get_last();
                throw new AutoloaderException_Include("Failed to include $path for $class: $error[message]");
                
            }
        }

        
        if (! (class_exists($class, false) || interface_exists($class, false))) {
            throw new AutoloaderException_Include_ClassNotDefined($class);
            
        }
        
        try {
            $reflectionClass = new ReflectionClass($class);
            $static = $reflectionClass->getMethod(self::CLASS_CONSTRUCTOR);
            if ($static->isStatic() && $static->getDeclaringClass()->getName() == $reflectionClass->getName()) {
                eval($class.'::'.self::CLASS_CONSTRUCTOR.'();');
            
            }
            
        } catch (ReflectionException $e) {
            // No class constructor
            
        }
    }
    
    
}