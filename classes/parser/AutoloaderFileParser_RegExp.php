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


InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileParser',
    dirname(__FILE__).'/AutoloaderFileParser.php'
);


/**
 * Implementation using a regulare expression.
 * 
 * This is not as reliable as the AutoloaderFileParser_Tokenizer.
 * But if there's not tokenizer support this is a well working
 * fallback. This class is as well as the regular expression
 * '~\s*((abstract\s+)?class|interface)\s+'.$class.'[$\s#/{]~im'.
 *
 * TODO Support for namespaces is missing.
 * 
 * @see AutoloaderFileParser_Tokenizer
 */
class AutoloaderFileParser_RegExp extends AutoloaderFileParser {
	
	
	/**
     * @return bool
     */
    static public function isSupported() {
    	return true;
    }


    /**
     * @param String $source
     * @return Array found classes in the source
     * @throws AutoloaderException_Parser
     */
    public function getClassesInSource($source) {
        $classes = array();
        $pattern =
            '~\s*((abstract\s+)?class|interface)\s+([a-z].*)[$\s#/{]~imU';
        preg_match_all($pattern, $source, $matches);
        foreach ($matches[3] as $class) {
            $classes[] = $class;

        }
        return $classes;
    }

	
}