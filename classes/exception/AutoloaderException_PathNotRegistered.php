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


require_once dirname(__FILE__).'/AutoloaderException.php';


/**
 * There was no Autoloader registered for the given path.
 */
class AutoloaderException_PathNotRegistered extends AutoloaderException {
    
	
	private
	/**
	 * @var String
	 */
	$path = '';
	
	
	/**
	 * @param String $path
	 */
	public function __construct($path) {
		parent::__construct("Did not find an Autoloader for '$path'");
		
		$this->path = $path;
	}
	
	
	/**
	 * @return String
	 */
    public function getPath() {
    	return $this->path;
    }

    
}