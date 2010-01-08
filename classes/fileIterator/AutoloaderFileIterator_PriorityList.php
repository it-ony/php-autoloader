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
 * Searches all files and returns them in a priority list.
 * 
 * This AutoloaderFileIterator searches all files in advance and
 * orders them. It may not be practicable on a huge file base.
 * 
 * @package autoloader
 * @subpackage spider
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 * @version 1.0
 */
class AutoloaderFileIterator_PriorityList extends AutoloaderFileIterator {
	
	
	private
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
	    $this->classname = $classname;
	}
	
	
	/**
	 * Files which match agaings $pattern are prefered
	 * during iteration.
	 * 
	 * @param String $pattern a RegExp
	 */
	public function addPreferedPattern($pattern) {
	    $this->preferedPatterns[] = $pattern;
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
        $simpleIterator = new AutoloaderFileIterator_Simple();
        $simpleIterator->setAutoloader($this->autoloader);
        $simpleIterator->skipFilesize = $this->skipFilesize;
        $simpleIterator->skipPatterns = $this->skipPatterns;
        
        // prefere Files which match agains the patterns in $preferedPatterns
        $preferedList   = array();
        $unpreferedList = array();
        foreach ($simpleIterator as $file) {
            foreach ($this->preferedPatterns as $pattern) {
                if (preg_match($pattern, $file)) {
                    $preferedList[] = $file;
                    continue 2;
                    
                }
            }
            $unpreferedList[] = $file;
            
        }
        
        //TODO order by $classname
        
        $files = array_merge($preferedList, $unpreferedList);
        
        $this->iterator = new ArrayIterator($files);
    }
    
    
    /**
     * @return bool
     */
    public function valid() {
        return $this->iterator->valid();
    }

    
}