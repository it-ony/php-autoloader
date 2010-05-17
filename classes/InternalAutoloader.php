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
# along with this program.                                              #
# If not, see <http://php-autoloader.malkusch.de/en/license/>.          #
#########################################################################


require_once dirname(__FILE__).'/AbstractAutoloader.php';
require_once dirname(__FILE__).'/exception/AutoloaderException_InternalClassNotLoadable.php';


/**
 * An Autoloader for internal classes
 * 
 * @package autoloader
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 * @version 1.0
 */
class InternalAutoloader extends AbstractAutoloader {

	
	static private
	/**
	 * @var InternalAutoloader
	 */
	$instance;
	
	
	private
	/**
     * @var array
     */
    $classes = array();
    
	
	static public function __static() {
		self::$instance = new self();
		self::$instance->register();
	} 
	
	/**
	 * @return InternalAutoloader
	 */
	static public function getInstance() {
		return self::$instance;
	}
	
	
	/**
     * @return Array all registered Autoloader instances which are doing their jobs
     * @see register()
     */
    static public function getRegisteredAutoloaders() {
    	$autoloaders = array();
    	foreach(parent::getRegisteredAutoloaders() as $autoloader) {
    		if ($autoloader instanceof self) {
    			$autoloaders[] = $autoloader;
    			
    		}
    	}
    	return $autoloaders;
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
     * This is a Singleton
     */
    private function __construct() {
	}
	

	/**
     * This is a Singleton
     */
	private function __clone() {
	}
	
	
	/**
     * This is used for internal classes, which cannot
     * use the Autoloader. They will be required in a 
     * traditional way without any index or searching.
     * 
     * @param String $class
     * @param String $path
     */
    public function registerClass($class, $path) {
        Autoloader::normalizeClass($class);
        $this->classes[$class] = $path;
    }
    
    
    /**
     * @param String $class
     * @throws AutoloaderException_InternalClassNotLoadable
     * @throws AutoloaderException_Include
     * @throws AutoloaderException_Include_FileNotExists
     * @throws AutoloaderException_Include_ClassNotDefined
     */
    protected function __autoload($class) {
    	if (!  array_key_exists($class, $this->classes)) {
    		throw new AutoloaderException_InternalClassNotLoadable($class);
    		
    	}
    	$this->loadClass($class, $this->classes[$class]);
    }
	
	
}


InternalAutoloader::__static();