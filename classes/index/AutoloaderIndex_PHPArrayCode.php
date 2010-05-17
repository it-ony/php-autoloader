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
	'AutoloaderIndex_File',
    dirname(__FILE__).'/AutoloaderIndex_File.php'
);


/**
 * The index is a serialized hashtable.
 * 
 * This index is working in every PHP environment. It should be fast enough
 * for most applications. The index is a file in the temporary directory.
 * The content of this file is PHP code which produces the index.
 *
 * This implementation uses eval() to execute the code of the index file.
 * If other users have access to the index file your application might
 * easily compromitted by this index. This index should really not be used!
 * 
 * This implementation is threadsafe.
 * 
 * @see AutoloaderIndex_IniFile
 */
class AutoloaderIndex_PHPArrayCode extends AutoloaderIndex_File {
    
    
    /**
     * @param String $data
     * @return Array
     * @throws AutoloaderException_Index
     */
    protected function buildIndex($data) {
        $index = eval($data);
        if (! is_array($index)) {
            $error = "{$this->getIndexPath()} failed to generate the index:"
                   . " $data";
            throw new AutoloaderException_Index($error);

        }
        return $index;
    }


    /**
     * @return String
     */
    protected function serializeIndex(Array $index) {
        $code = 'return array('."\n";
        foreach ($index as $class => $path) {
            $safePath = stripslashes($path);
            $code .= "    '$class' => '$safePath',\n";

        }
        $code .= ');';
        return $code;
    }


}