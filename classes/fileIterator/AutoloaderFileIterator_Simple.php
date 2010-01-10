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
    'AutoloaderFileIterator',
    dirname(__FILE__).'/AutoloaderFileIterator.php'
);


/**
 * Searches all files without any logic.
 */
class AutoloaderFileIterator_Simple extends AutoloaderFileIterator {
	
	
	private
	/**
	 * @var Array
	 */
	$stack = array(),
	/**
	 * @var DirectoryIterator
	 */
	$iterator;
	
	
	/**
	 * @return String
	 */
	public function current () {
	    return $this->iterator->current()->getPathname();
	}
	

	/**
     * @return String
     */
	public function key() {
		return $this->iterator->key();
	}
	
	
    public function next() {
    	$this->iterator->next();
    }
    
    
    public function rewind() {
        $this->stack    = array();
    	$this->iterator = new DirectoryIterator($this->autoloader->getPath());
    	$this->iterator->rewind();
    }
    
    
    /**
     * @return bool
     */
    public function valid() {
        while(true) {
            if (is_null($this->iterator)) {
                return false;
                
            }
            
            // recurse backwards
            if (! $this->iterator->valid()) {
                $this->iterator = array_pop($this->stack);
                continue;
                
            }
            
            $path = $this->iterator->current()->getPathname();
            
            // apply file filters
            foreach ($this->skipPatterns as $pattern) {
                if (preg_match($pattern, $path)) {
                    $this->iterator->next();
                    continue 2;
                    
                }
                
            }
            
            // skip . and ..
            if (in_array($this->iterator->current()->getFilename(), array('.', '..'))) {
                $this->iterator->next();
                continue;
                
            }
            
            // recurse through the directories
            if ($this->iterator->current()->isDir()) {
                $this->iterator->next();
                $this->stack[]  = $this->iterator;
                $this->iterator = new DirectoryIterator($path);
                $this->iterator->rewind();
                continue;
                
            }
            
            // skip too big files
            if (! empty($this->skipFilesize) && $this->iterator->current()->getSize() > $this->skipFilesize) {
                $this->iterator->next();
                continue;
                
            }
            
        	return true;
        }
    }

    
}