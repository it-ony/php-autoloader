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
 * @see AutoloaderFileParser_Tokenizer
 * @version 1.2
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
        // Namespaces are searched.
        $namespaces         = array();
        $namespacePattern   =
            '~namespace\s+([^\s;{]+)~im';
        preg_match_all(
            $namespacePattern,
            $source,
            $namespaceMatches,
            PREG_OFFSET_CAPTURE
        );
        foreach ($namespaceMatches[1] as $namespaceMatch) {
            $namespace  = $namespaceMatch[0];
            $offset     = $namespaceMatch[1];

            $namespaces[$offset] = $namespace;

        }

        // Classes and interfaces are searched.
        $classes        = array();
        $classPattern   =
            '~\s*((abstract\s+)?class|interface)\s+([a-z].*)[$\s#/{]~imU';
        preg_match_all(
            $classPattern,
            $source,
            $classMatches,
            PREG_OFFSET_CAPTURE
        );
        foreach ($classMatches[3] as $classMatch) {
            $class  = $classMatch[0];
            $offset = $classMatch[1];

            // The appropriate will be prepended.
            $classNamespace = '';
            foreach ($namespaces as $namespaceOffset => $namespace) {
                if ($namespaceOffset > $offset) {
                    break;

                }
                $classNamespace = $namespace . "\\";

            }
            
            $classes[] = $classNamespace . $class;

        }
        return $classes;
    }

	
}