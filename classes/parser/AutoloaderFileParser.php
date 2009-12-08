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


Autoloader::registerInternalClass(
    'AutoloaderException_Parser_IO',
    dirname(__FILE__).'/exception/AutoloaderException_Parser_IO.php'
);
Autoloader::registerInternalClass(
    'AutoloaderFileParser_Tokenizer',
    dirname(__FILE__).'/AutoloaderFileParser_Tokenizer.php'
);
Autoloader::registerInternalClass(
    'AutoloaderFileParser_RegExp',
    dirname(__FILE__).'/AutoloaderFileParser_RegExp.php'
);


/**
 * A Parser for Class definition
 * 
 * An implementation of this class should be able to parse a file and
 * find a class definition.
 * 
 * @package autoloader
 * @subpackage parser
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 * @version 1.0
 */
abstract class AutoloaderFileParser {
	
	
	/**
	 * @param String $class
	 * @param String $source The source as a string. This is the content of a file.
	 * @return bool True if the class $class was found in the source $source.
	 * @throws AutoloaderException_Parser
	 */
	abstract public function isClassInSource($class, $source);
    /**
     * @return bool True if this implementation is supported by the current PHP environment
     */
    abstract static public function isSupported();
    
	
	/**
	 * @see AutoloaderFileParser_Tokenizer
	 * @see AutoloaderFileParser_RegExp
	 * @return AutoloaderFileParser AutoloaderFileParser_Tokenizer if suported else AutoloaderFileParser_RegExp
	 */
	static public function getInstance() {
		if (AutoloaderFileParser_Tokenizer::isSupported()) {
			return new AutoloaderFileParser_Tokenizer();
			
		} else {
			return new AutoloaderFileParser_RegExp();
			
		}
	}
	
	
	/**
	 * @param String $class
	 * @param String $file
	 * @return bool True if the class $class was found in the file $file.
	 * @throws AutoloaderException_Parser_IO
	 * @throws AutoloaderException_Parser
	 */
	public function isClassInFile($class, $file) {
        $source = @file_get_contents($file);
        if ($source === false) {
        	$error = error_get_last();
            throw new AutoloaderException_Parser_IO(
                "Could not read $file while searching $class: $error[message]"
            );
                    
        }
        return $this->isClassInSource($class, $source);
	}
	
	
}