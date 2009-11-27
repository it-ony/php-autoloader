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
    'AutoloaderException_Index',
    dirname(__FILE__).'/exception/AutoloaderException_Index.php'
);
Autoloader::registerInternalClass(
	'AutoloaderException_Index_NotFound',
    dirname(__FILE__).'/exception/AutoloaderException_Index_NotFound.php'
);
Autoloader::registerInternalClass(
    'AutoloaderException_Index_IO',
    dirname(__FILE__).'/exception/AutoloaderException_Index_IO.php'
);


/**
 * The index is a serialized hashtable.
 * 
 * This index is working in every PHP environment. It should be fast enough
 * for most applications. The index is a file in the temporary directory.
 * The content of this file is a serialized Hashtable.
 * 
 * @see serialize()
 * @see unserialize()
 */
class AutoloaderIndex_SerializedHashtable extends AutoloaderIndex {
    
    
	/**
	 * The prefix of the index file
	 */
    const FILE_PREFIX = 'autoload_hash_';
    
    
    private
    /**
     * @var String
     */
    $path = '',
    /**
     * @var Array
     */
    $index = null;
    
    
    /**
     * Set the path to the index file.
     * 
     * Setting the index file path is optional. Per default
     * it will be a file in the temporary directory. Its
     * prefix is AutoloaderIndex_SerializedHashtable::FILE_PREFIX.
     * 
     * @param String $path the path to the index file
     * @see getIndexPath()
     */
    public function setIndexPath($path) {
    	$this->path  = $path;
    	$this->index = null;
    }
    
    
    /**
     * Get the path of the index file.
     * 
     * @return String The path to the index file
     * @see setIndexPath()
     */
    public function getIndexPath() {
    	if (empty($this->path)) {
    		$this->setIndexPath(
    		    sys_get_temp_dir()
    		    . DIRECTORY_SEPARATOR
    		    . self::FILE_PREFIX
    		    . $this->getContext()
            );
    		
    	}
    	return $this->path;
    }
    
    
    /**
     * Deletes the index file
     * 
     * @throws AutoloaderException_Index Deleting failed
     */
    public function delete() {
    	if (! unlink($this->getIndexPath())) {
    		throw new AutoloaderException_Index("Could not delete {$this->getIndexPath()}.");
    		
    	}
    	$this->index = null;
    }
    
    
	/**
     * @throws AutoloaderException_Index
     */
    private function assertLoadedIndex() {
        if (is_array($this->index)) {
            return;
            
        }
        
        try {
	        $serializedIndex = $this->readFile($this->getIndexPath());
            $index           = unserialize($serializedIndex);
            
	        if (! is_array($index)) {
	            throw new AutoloaderException_Index("Can not unserialize {$this->getIndexPath()}: $serializedIndex");
	            
	        }
	        $this->index = $index;
	        
    	} catch (AutoloaderException_Index_IO $e) {
    		if (file_exists($this->getIndexPath())) {
                throw new AutoloaderException_Index_IO("Could not read Index {$this->getIndexPath()}.");
                    
            }
            $this->index = array();
    		
    	}
    }
    
    
    /**
     * @return String
     * @throws AutoloaderException_Index_IO
     */
    protected function readFile($file) {
        $serializedIndex = @file_get_contents($file);
        if ($serializedIndex === false) {
            throw new AutoloaderException_Index_IO($file);
                
        }
        return $serializedIndex;
    }
    
    
    /**
     * @return int written Bytes
     * @throws AutoloaderException_Index_IO
     */
    protected function saveFile($file, $serializedIndex) {
    	return @file_put_contents($file, $serializedIndex);
    }
    
    
    /**
     * @throws AutoloaderException_Index_IO
     */
    public function save() {
        $serializedIndex = serialize($this->index);
        
        /* Avoid race conditions, by writting into a temporary file
         * which will be moved atomically
         */
        $tmpFile = tempnam(dirname($this->getIndexPath()), get_class($this));
        if (! $tmpFile) {
            throw new AutoloaderException_Index_IO(
                "Could not create temporary file in " . dirname($file)
                . " for saving new index atomically."
            );
            
        }
        
        $writtenBytes = $this->saveFile($tmpFile, $serializedIndex);
        if ($writtenBytes !== strlen($serializedIndex)) {
            throw new AutoloaderException_Index_IO(
                "Could not save new index to $tmpFile. $writtenBytes Bytes written."
            );
            
        }
        
        if (! rename($tmpFile, $this->getIndexPath())) {
        	throw new AutoloaderException_Index_IO(
                "Could not move new index $tmpFile to {$this->getIndexPath()}."
            );
        	
        }
    }
    
    
    /**
     * @param String $class
     * @throws AutoloaderException_Index
     * @throws AutoloaderException_Index_NotFound
     * @return String The absolute path
     */
    public function getPath($class) {
        $this->assertLoadedIndex();
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
        $this->assertLoadedIndex();
        $this->index[$class] = $path;
    }
    
    
	/**
     * @param String $class
     * @throws AutoloaderException_Index
     */
    protected function _unsetPath($class) {
        $this->assertLoadedIndex();
        unset($this->index[$class]);
    }
    
    
    /**
     * @param String $class
     * @throws AutoloaderException_Index
     * @return bool
     */
    public function hasPath($class) {
        $this->assertLoadedIndex();
        return array_key_exists($class, $this->index);
    }


}