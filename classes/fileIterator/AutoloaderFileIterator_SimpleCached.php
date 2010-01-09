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
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileIterator_Simple',
    dirname(__FILE__).'/AutoloaderFileIterator_Simple.php'
);


/**
 * This AutoloaderFileIterator uses AutoloaderFileIterator_Simple. It caches
 * the result in an array.
 * 
 * @see AutoloaderFileIterator_Simple
 */
class AutoloaderFileIterator_SimpleCached extends AutoloaderFileIterator {
	
	
	private
	/**
	 * @var Array
	 */
	$foundFiles = array(),
	/**
	 * @var Iterator
	 */
	$iterator;
	
	
	protected function reset() {
	    parent::reset();
	    
	    $this->foundFiles  = array();
	    $this->iterator    = new AutoloaderFileIterator_Simple();
	    
	    $this->iterator->setSkipFilesize($this->skipFilesize);
	    $this->iterator->skipPatterns = $this->skipPatterns;
	    if (! is_null($this->autoloader)) {
	       $this->iterator->setAutoloader($this->autoloader);
	       
	    }
	}
	
	
	/**
	 * @return String
	 */
	public function current () {
	    return $this->iterator->current();
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
        $this->foundFiles = array();
    	$this->iterator->rewind();
    }
    
    
    /**
     * @return bool
     */
    public function valid() {
        if (! $this->iterator instanceof AutoloaderFileIterator_Simple) {
            return $this->iterator->valid();
            
        }
        if ($this->iterator->valid()) {
            $this->foundFiles[$this->current()] = $this->current();
            return true;
            
        } else {
            $this->iterator = new ArrayIterator($this->foundFiles);
            return false;
            
        }
    }

    
}