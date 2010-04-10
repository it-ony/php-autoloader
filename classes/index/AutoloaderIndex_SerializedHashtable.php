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
	'AutoloaderIndex',
    dirname(__FILE__).'/AutoloaderIndex.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index',
    dirname(__FILE__).'/exception/AutoloaderException_Index.php'
);
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderException_Index_NotFound',
    dirname(__FILE__).'/exception/AutoloaderException_Index_NotFound.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index_IO',
    dirname(__FILE__).'/exception/AutoloaderException_Index_IO.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index_IO_FileNotExists',
    dirname(__FILE__).'/exception/AutoloaderException_Index_IO_FileNotExists.php'
);


/**
 * The index is a serialized hashtable.
 * 
 * This index is working in every PHP environment. It should be fast enough
 * for most applications. The index is a file in the temporary directory.
 * The content of this file is a serialized Hashtable.
 * 
 * This implementation is threadsafe.
 * 
 * @see serialize()
 * @see unserialize()
 * @version 1.1
 */
class AutoloaderIndex_SerializedHashtable extends AutoloaderIndex {
    
    
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
    		    . get_class($this)
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
    	if (! @unlink($this->getIndexPath())) {
    		$error = error_get_last();
    		throw new AutoloaderException_Index("Could not delete {$this->getIndexPath()}: $error[message]");
    		
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
	        
    	} catch (AutoloaderException_Index_IO_FileNotExists $e) {
    		/*
    		 * This could happen. The index is reseted to an empty index.
    		 */
            $this->index = array();
    		
    	}
    }
    
    
    /**
     * @return String
     * @throws AutoloaderException_Index_IO
     * @throws AutoloaderException_Index_IO_FileNotExists
     */
    protected function readFile($file) {
        $serializedIndex = @file_get_contents($file);
        if ($serializedIndex === false) {
        	if (! file_exists($file)) {
        		throw new AutoloaderException_Index_IO_FileNotExists($file);
        		
        	} else {
        		$error = error_get_last();
                throw new AutoloaderException_Index_IO("Could not read '$file': $error[message]");
                
        	}
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
     * @since 1.1 save() is threadsafe. 
     */
    protected function _save() {
        $serializedIndex = serialize($this->index);
        
        /* Avoid race conditions, by writting into a temporary file
         * which will be moved atomically
         */
        $tmpFile = @tempnam(dirname($this->getIndexPath()), get_class($this) . "_tmp_");
        if (! $tmpFile) {
        	$error = error_get_last();
            throw new AutoloaderException_Index_IO(
                "Could not create temporary file in " . dirname($this->getIndexPath())
                . " for saving new index atomically: $error[message]"
            );
            
        }
        
        $writtenBytes = $this->saveFile($tmpFile, $serializedIndex);
        if ($writtenBytes !== strlen($serializedIndex)) {
        	$error = error_get_last();
            throw new AutoloaderException_Index_IO(
                "Could not save new index to $tmpFile. $writtenBytes Bytes written: $error[message]"
            );
            
        }
        
        if (! @rename($tmpFile, $this->getIndexPath())) {
        	$error = error_get_last();
        	throw new AutoloaderException_Index_IO(
                "Could not move new index $tmpFile to {$this->getIndexPath()}: $error[message]"
            );
        	
        }
    }
    
    
    /**
     * @throws AutoloaderException_Index
     * @return int the size of the index
     */
    public function count() {
    	$this->assertLoadedIndex();
        return count($this->index);
    }
    
    
    /**
     * @param String $class
     * @throws AutoloaderException_Index
     * @throws AutoloaderException_Index_NotFound
     * @return String The absolute path
     */
    protected function _getPath($class) {
        $this->assertLoadedIndex();
        if (! $this->hasPath($class)) {
            throw new AutoloaderException_Index_NotFound($class);    
            
        }
        return $this->index[$class];
    }


    /**
     * @throws AutoloaderException_Index
     * @return Array() All paths in the index
     */
    public function getPaths() {
        $this->assertLoadedIndex();
        return $this->index;
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