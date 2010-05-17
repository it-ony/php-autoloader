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


InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Parser',
    dirname(__FILE__).'/exception/AutoloaderException_Parser.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileParser',
    dirname(__FILE__).'/AutoloaderFileParser.php'
);


/**
 * Reliable implementation using PHP's tokenizer.
 * 
 * @see token_get_all()
 * @version 1.2
 */
class AutoloaderFileParser_Tokenizer extends AutoloaderFileParser {


    const NEXT_CLASS        = 'class';
    const NEXT_NAMESPACE    = 'namespace';
    const NEXT_NOTHING      = 'nothing';
	
	
    /**
     * @return bool
     */
    static public function isSupported() {
        return function_exists("token_get_all");
    }
    
    
	/**
	 * @param String $source
	 * @return Array found classes in the source
     * @throws AutoloaderException_Parser
	 */
	public function getClassesInSource($source) {
		$classes        = array();
		$nextStringType = self::NEXT_NOTHING;
        $namespace      = '';
		$tokens         = @token_get_all($source);

		if (! is_array($tokens)) {
			$error = error_get_last();
			throw new AutoloaderException_Parser(
                "Could not tokenize: $error[message]\n$source");

		}
		
		foreach ($tokens as $token) {
			if (! is_array($token)) {
				continue;
				
				
			}
            $tokenID    = $token[0];
            $tokenValue = $token[1];

			switch ($tokenID) {

                case T_NAMESPACE:
                    $namespace      = '';
                case T_NS_SEPARATOR:
                    $nextStringType = self::NEXT_NAMESPACE;
                    break;
				
				case T_INTERFACE:
				case T_CLASS:
					$nextStringType = self::NEXT_CLASS;
					break;
					
				case T_STRING:
                    $type           = $nextStringType;
                    $nextStringType = self::NEXT_NOTHING;
                    switch ($type) {

                        case self::NEXT_CLASS:
                            $classes[] = $namespace.$tokenValue;
                            break;

                        case self::NEXT_NAMESPACE:
                            $namespace .= "$tokenValue\\";
                            break;

                    }
					break;
				
			}
			
		}
		return $classes;
	}
	
	
}
