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
    'AutoloaderFileParser',
    dirname(__FILE__).'/AutoloaderFileParser.php'
);


/**
 * Reliable implementation using PHP's tokenizer.
 * 
 * @see token_get_all()
 */
class AutoloaderFileParser_Tokenizer extends AutoloaderFileParser {
	
	
    /**
     * @return bool
     */
    static public function isSupported() {
        return function_exists("token_get_all");
    }
    
    
	/**
	 * @param String $class
	 * @param String $source
	 * @return bool
	 */
	public function isClassInSource($class, $source) {
		$class                 = strtolower($class);
		$tokens                = token_get_all($source);
		$nextStringIsClassName = false;
		
		foreach ($tokens as $token) {
			if (! is_array($token)) {
				continue;
				
				
			}
            $tokenID    = $token[0];
            $tokenValue = $token[1];
				
			switch ($tokenID) {
				
				case T_INTERFACE:
				case T_CLASS:
					$nextStringIsClassName = true;
					break;
					
				case T_STRING:
					if ($nextStringIsClassName && strtolower($tokenValue) == $class) {
						return true;
						
					}
					$nextStringIsClassName = false;
					break;
				
			}
			
		}
		return false;
	}
	
	
}