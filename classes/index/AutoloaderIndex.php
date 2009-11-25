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
abstract class AutoloaderIndex {
    
    
    private
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
     * @throws AutoloaderException_Index_NotFound
     * @return String The absolute path
     */
    abstract public function getPath($class);
    /**
     * @param String $class
     * @throws AutoloaderException_Index
     * @return bool
     */
    abstract public function hasPath($class);
    /**
     * @throws AutoloaderException_Index
     */
    abstract public function delete();
    /**
     * @param String $class
     * @param String $path
     * @throws AutoloaderException_Index
     */
    abstract protected function _setPath($class, $path);
    /**
     * @param String $class
     * @throws AutoloaderException_Index
     */
    abstract protected function _unsetPath($class);
    /**
     * @throws AutoloaderException_Index
     */
    abstract protected function save();

    
    public function setAutoloader(Autoloader $autoloader) {        
        $this->autoloader = $autoloader;
    }
    
    
    /**
     * @throws AutoloaderException_Index
     */
    public function __destruct() {
        if ($this->isChanged) {
            $this->save();
            
        }
    }
    
    
    /**
     * @param String $class
     * @param String $path
     * @throws AutoloaderException_Index
     */
    public function setPath($class, $path) {
        $this->_setPath($class, $path);
        $this->isChanged = true;
    }
    
    
	/**
     * @param String $class
     * @throws AutoloaderException_Index
     */
    public function unsetPath($class) {
        $this->_unsetPath($class);
        $this->isChanged = true;
    }
    
    
}