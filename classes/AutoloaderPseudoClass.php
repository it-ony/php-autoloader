<?php
##########################################################################
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


Autoloader::registerInternalClass(
    'AutoloaderException',
    dirname(__FILE__).'/exception/AutoloaderException.php'
);


/**
 * The autoloader returns a pseudo class if no class definition was found.
 * 
 * To avoid terminating with an fatal error, the autoloader returns a
 * derivation of this class. Every operation on this class results in
 * throwing a Exception.
 * 
 * @package autoloader
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 * @version 1.0
 */
abstract class AutoloaderPseudoClass {
	
	
	/**
	 * @throws AutoloaderException
	 */
	abstract protected function throwException();
	/**
	 * @throws AutoloaderException
	 */
	abstract public static function __callStatic();
	
	
	/**
	 * Defines a class $class which extends the AutoloaderPseudoClass.
	 * 
	 * @param String $class
	 */
	public static function defineDerivation($class, AutoloaderException $exception) {
		eval('
            class '.$class.' extends AutoloaderPseudoClass {
                
                
                private static $exception;
                
                
                public static function setException(AutoloaderException $exception) {
                    self::$exception = $exception;
                }
                
                    
                protected function throwException() {
                    throw self::$exception;
                }
                    
                    
                public static function __callStatic() {
                    throw self::$exception;
                }
                
            }
            
            
            '.$class.'::setException($exception);
        ');
	}
	
	
	/**
	 * @throws AutoloaderException
	 */
	public function __construct() {
		$this->throwException();
	}

	
	/**
	 * @throws AutoloaderException
	 */
	public function __call($name, $arguments) {
		$this->throwException();
	}
	
	
	/**
	 * @throws AutoloaderException
	 */
	public function __get($name) {
		$this->throwException();
	}
	
	
	/**
	 * @throws AutoloaderException
	 */
	public function __set($name, $value) {
		$this->throwException();
	}
	
	
	/**
	 * @throws AutoloaderException
	 */
	public function __isset($name) {
		$this->throwException();
	}
	
	
	/**
	 * @throws AutoloaderException
	 */
	public function __unset($name) {
		$this->throwException();
	}
	
	
	/**
	 * @throws AutoloaderException
	 */
	public function __wakeup() {
		$this->throwException();
	}
	
	
	/**
	 * @throws AutoloaderException
	 */
	public function __invoke() {
		$this->throwException();
	}
	
	
}