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
 * @subpackage Index
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 */


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


class AutoloaderIndex_SerializedHashtable extends AutoloaderIndex {
    
    
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
     * @param Strint $path
     */
    public function setIndexPath($path) {
    	$this->path  = $path;
    	$this->index = null;
    }
    
    
    /**
     * @return String
     */
    public function getIndexPath() {
    	if (empty($this->path)) {
    		$this->setIndexPath(
    		    sys_get_temp_dir()
    		    . DIRECTORY_SEPARATOR
    		    . self::FILE_PREFIX
    		    . md5( implode('', $this->autoloader->getPaths()) )
            );
    		
    	}
    	return $this->path;
    }
    
    
    /**
     * @throws AutoloaderException_Index
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
    protected function save() {
        $serializedIndex = serialize($this->index);
        $writtenBytes    = $this->saveFile($this->getIndexPath(), $serializedIndex);
        if ($writtenBytes !== strlen($serializedIndex)) {
            throw new AutoloaderException_Index_IO("Could not save to {$this->getIndexPath()}. $writtenBytes Bytes written.");
            
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