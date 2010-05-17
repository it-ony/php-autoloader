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
    'AutoloaderFileIterator',
    dirname(__FILE__).'/AutoloaderFileIterator.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileIterator_Simple',
    dirname(__FILE__).'/AutoloaderFileIterator_Simple.php'
);


/**
 * Searches all files and returns them in a priority list.
 * 
 * This AutoloaderFileIterator searches all files in advance and
 * orders them. It may not be practicable on a huge file base.
 */
class AutoloaderFileIterator_PriorityList extends AutoloaderFileIterator {
	
	
	private
	/**
	 * @var Array
	 */
	$preferedFiles = array(),
	/**
	 * @var Array
	 */
	$unpreferedFiles = array(),
	/**
	 * @var String
	 */
	$classname = '',
	/**
	 * @var Array
	 */
	$preferedPatterns = array('~\.(php|inc)$~i'),
	/**
	 * @var ArrayIterator
	 */
	$iterator;
	
	
	/**
	 * Iteration tries to return an ordered list to
	 * have potential class definition candidates first.  
	 * 
	 * @param String $classname
	 */
	public function setClassname($classname) {
	    $this->classname = strtolower($classname);
	}
	
	
	/**
	 * Files which match agaings $pattern are prefered
	 * during iteration.
	 * 
	 * @param String $pattern a RegExp
	 */
	public function addPreferedPattern($pattern) {
	    $this->preferedPatterns[] = $pattern;
	    $this->reset();
	}
	
	
	protected function reset() {
	    parent::reset();
	    
	    unset($this->preferedFiles);
        unset($this->unpreferedFiles);
        unset($this->iterator);
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
        $this->initFileArrays();
        
        // order by Levenshtein distance to $classname
        $levArray = array();
        foreach ($this->preferedFiles as $file) {
            $levArray[] = levenshtein(strtolower(basename($file)), $this->classname);
            
        }
        array_multisort($levArray, $this->preferedFiles);
        
        
        // merge ordered and unordered files
        $files = array_merge($this->preferedFiles, $this->unpreferedFiles);
        
        $this->iterator = new ArrayIterator($files);
    }
    
    
    /**
     * @return bool
     */
    public function valid() {
        return ! is_null($this->iterator) && $this->iterator->valid();
    }
    
    
    /**
     * @return Array
     */
    private function initFileArrays() {
        if (! empty($this->preferedFiles) || ! empty($this->unpreferedFiles)) {
            return;
            
        }
        $simpleIterator = new AutoloaderFileIterator_Simple();
        $simpleIterator->setAutoloader($this->autoloader);
        $simpleIterator->skipFilesize = $this->skipFilesize;
        $simpleIterator->skipPatterns = $this->skipPatterns;
        
        $this->preferedFiles   = array();
        $this->unpreferedFiles = array();
        foreach ($simpleIterator as $file) {
            foreach ($this->preferedPatterns as $pattern) {
                if (preg_match($pattern, $file)) {
                    $this->preferedFiles[] = $file;
                    continue 2;
                    
                }
            }
            $this->unpreferedFiles[] = $file;
            
        }
    }

    
}