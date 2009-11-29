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
    'AutoloaderException',
    dirname(__FILE__).'/AutoloaderException.php'
);


/**
 * Indicates Exceptions during class search.
 * 
 * AutoloaderException_SearchFailed is thrown internally by the Autoloader
 * if it fails during autoloading to find a class.
 * 
 * @version 1.1
 */
class AutoloaderException_SearchFailed extends AutoloaderException {
	
	
	private
	/**
	 * @var String
	 */
    $class = '';
    
    
    /**
     * @param String $class
     */
    public function __construct($class) {
    	parent::__construct("Couldn't find class $class.");
    	
    	$this->class = $class;
    }
    
    
    /**
     * The class which wasn't found.
     * 
     * @return String
     * @since 1.1
     */
    public function getClass() {
    	return $this->class;
    }

    
}