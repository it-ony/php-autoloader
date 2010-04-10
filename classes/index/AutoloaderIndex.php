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


/**
 * The AutoloaderIndex stores the location of class defintions for speeding up recurring searches.
 * 
 * Searching a class definition in the filesystem takes a lot of time, as every
 * file is read. To avoid these long searches, a found class definition will be stored
 * in an index. The next search for an already found class definition will take no
 * time.
 * 
 * @package autoloader
 * @subpackage index
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 * @version 1.1
 */
abstract class AutoloaderIndex implements Countable {
    
    
    private
    /**
     * @var int counts how often getPath() is called
     * @see getPath()
     */
    $getPathCallCounter = 0,
    /**
     * @var bool
     */
    $isChanged = false;
    
    
    protected
    /**
     * @var Autoloader
     */
    $autoloader;
    
    
    /**
     * @param String $class
     * @throws AutoloaderException_Index
     * @throws AutoloaderException_Index_NotFound the class is not in the index
     * @return String The absolute path of the found class $class
     * @see getPath()
     */
    abstract protected function _getPath($class);
    /**
     * @param String $class
     * @throws AutoloaderException_Index
     * @return bool True if the class $class is already stored in the index 
     */
    abstract public function hasPath($class);
    /**
     * @throws AutoloaderException_Index
     * @return Array() All paths in the index
     */
    abstract public function getPaths();
    /**
     * Deletes the index
     * 
     * @throws AutoloaderException_Index
     */
    abstract public function delete();
    /**
     * Set the path for the class $class to $path
     * 
     * This must not yet be persistent to the index. The Destructor
     * will call save() to make it persistent.
     * 
     * @param String $class
     * @param String $path
     * @throws AutoloaderException_Index
     * @see save()
     * @see _unsetPath()
     */
    abstract protected function _setPath($class, $path);
    /**
     * Unset the path for the class $class.
     * 
     * This must not yet be persistent to the index. The Destructor
     * will call save() to make it persistent.
     * 
     * @param String $class
     * @throws AutoloaderException_Index
     * @see _setPath()
     * @see save()
     */
    abstract protected function _unsetPath($class);
    /**
     * Makes the changes to the index persistent.
     * 
     * The destructor is calling this method.
     * 
     * @throws AutoloaderException_Index
     * @see save()
     */
    abstract protected function _save();
    
    
    /**
     * @param String $class
     * @throws AutoloaderException_Index
     * @throws AutoloaderException_Index_NotFound the class is not in the index
     * @return String The absolute path of the found class $class
     * @see _getPath()
     */
    public function getPath($class) {
    	$this->getPathCallCounter++;
    	return $this->_getPath($class);
    }
    
    
    /**
     * @return int A counter how often getPath() has been called
     * @see getPath()
     */
    public function getGetPathCallCounter() {
    	return $this->getPathCallCounter;
    }
    
    
    
    /**
     * Makes the changes to the index persistent.
     * 
     * The destructor is calling this method.
     * 
     * @throws AutoloaderException_Index
     * @see _setPath()
     * @see _unsetPath()
     * @see __destruct()
     * @see _save()
     */
    public function save() {
    	if (! $this->isChanged) {
    		return;
    		
    	}
    	$this->_save();
    	$this->isChanged = false;
    }

    
    /**
     * The Autoloader calls this to set itself to this index.
     * 
     * @see Autoloader::setIndex()
     */
    public function setAutoloader(Autoloader $autoloader) {        
        $this->autoloader = $autoloader;
    }
    
    
    /**
     * Destruction of this index will make changes persistent.
     * 
     * @throws AutoloaderException_Index
     * @see save()
     */
    public function __destruct() {
        $this->save();
    }
    
    
    /**
     * Set the path for the class $class to $path
     * 
     * This must not yet be persistent to the index. The Destructor
     * will call save() to make it persistent.
     * 
     * @param String $class
     * @param String $path
     * @throws AutoloaderException_Index
     * @see save()
     * @see __destruct()
     * @see _setPath()
     * @see unsetPath()
     */
    public function setPath($class, $path) {
        $this->_setPath($class, $path);
        $this->isChanged = true;
    }
    
    
	/**
	 * Unset the path for the class
     * 
     * This must not yet be persistent to the index. The Destructor
     * will call save() to make it persistent.
     * 
     * @param String $class
     * @throws AutoloaderException_Index
     * @see _unsetPath()
     * @see __destruct()
     * @see setPath()
     * @see save()
     */
    public function unsetPath($class) {
        $this->_unsetPath($class);
        $this->isChanged = true;
    }
    
    
    /**
     * The Autoloader class path context
     * 
     * Only Autoloaders with an equal class path work in the same context.
     * 
     * @return String A context to distinguish different autoloaders
     */
    protected function getContext() {
        return md5($this->autoloader->getPath());
    }
    
    
}