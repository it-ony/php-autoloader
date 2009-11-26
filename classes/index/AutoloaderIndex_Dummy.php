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
	'AutoloaderIndex',
    dirname(__FILE__).'/AutoloaderIndex.php'
);
Autoloader::registerInternalClass(
	'AutoloaderException_Index_NotFound',
    dirname(__FILE__).'/exception/AutoloaderException_Index_NotFound.php'
);


/**
 * A Dummy implementation without any persistent abilities.
 * 
 * There is no sense except testing in using this index.
 */
class AutoloaderIndex_Dummy extends AutoloaderIndex {
    
    
    private
    /**
     * @var Array
     */
    $index = array();
    
    
    
    /**
     * @param String $class
     * @throws AutoloaderException_Index
     * @throws AutoloaderException_Index_NotFound
     * @return String The absolute path
     */
    public function getPath($class) {
        if (! $this->hasPath($class)) {
            throw new AutoloaderException_Index_NotFound($class);    
            
        }
        return $this->index[$class];
    }
    
    
    /**
     * @param String $class
     * @param String $path
     * @throws AutoloaderException_Index
     */
    protected function _setPath($class, $path) {
        $this->index[$class] = $path;
    }
    
    
	/**
     * @param String $class
     * @throws AutoloaderException_Index
     */
    protected function _unsetPath($class) {
        unset($this->index[$class]);
    }
    
    
    /**
     * @param String $class
     * @throws AutoloaderException_Index
     * @return bool
     */
    public function hasPath($class) {
        return array_key_exists($class, $this->index);
    }
    
    
    /**
     * Does nothing
     */
    public function delete() {
    }

    
    /**
     * Does nothing
     */
    protected function save() {
    }
    

}