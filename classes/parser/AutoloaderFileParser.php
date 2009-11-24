<?php
/**
 * Copyright (C) 2010  Markus Malkusch <markus@malkusch.de>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package Autoloader
 * @subpackage parser
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 */


Autoloader::registerInternalClass(
    'AutoloaderException_Parser_IO',
    dirname(__FILE__).'/../exception/AutoloaderException_Parser_IO.php'
);
Autoloader::registerInternalClass(
    'AutoloaderFileParser_Tokenizer',
    dirname(__FILE__).'/AutoloaderFileParser_Tokenizer.php'
);
Autoloader::registerInternalClass(
    'AutoloaderFileParser_RegExp',
    dirname(__FILE__).'/AutoloaderFileParser_RegExp.php'
);


abstract class AutoloaderFileParser {
	
	
	/**
	 * @param String $class
	 * @param String $source
	 * @return bool
	 * @throws AutoloaderException_Parser
	 */
	abstract public function isClassInSource($class, $source);
    /**
     * @return bool
     */
    abstract static public function isSupported();
    
	
	/**
	 * @return AutoloaderFileParser
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
	 * @return bool
	 * @throws AutoloaderException_Parser_IO
	 * @throws AutoloaderException_Parser
	 */
	public function isClassInFile($class, $file) {
        $source = file_get_contents($file);
        if ($source === false) {
            throw new AutoloaderException_Parser_IO("Could not read $file while searching $class.");
                    
        }
        return $this->isClassInSource($class, $source);
	}
	
	
}