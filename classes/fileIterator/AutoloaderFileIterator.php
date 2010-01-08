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
    'Autoloader',
    dirname(__FILE__).'/../Autoloader.php'
);


/**
 * An AutoloaderFileIterator finds potential files with class definitions.
 * 
 * @package autoloader
 * @subpackage spider
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 * @version 1.0
 */
abstract class AutoloaderFileIterator implements Iterator {
	
	
	protected
	/**
     * @var int Skip files greater than 1MB as default
     */
    $skipFilesize = 1048576,
    /**
     * @var Array ignore SVN, CVS, *.dist and multimedia files
     */
    $skipPatterns = array(
        '~/\.svn/~',
        '~/\.CVS/~',
        '~\.(dist|jpe?g|gif|png|svg|og[gm]|mp3|wav|mpe?g)$~i',
    ),
	/**
	 * @var String
	 */
	$autoloader;
	
	
	public function setAutoloader(Autoloader $autoloader) {
		$this->autoloader = $autoloader;
	}
	
	
    /**
     * Adds a regular expression for ignoring files in the class paths.
     * 
     * Files which paths match one of these patterns won't be
     * searched for class definitions.
     * 
     * This is useful for version control paths where files
     * with class definitions exists.
     * Subversion (.svn) and CVS (.CVS) are excluded by default.
     * 
     * @param String $pattern a regular expression including delimiters
     * @see $skipPatterns
     */
    public function addSkipPattern($pattern) {
        $this->skipPatterns[] = $pattern;
    }
    
    
    /**
     * Set a file size to ignore files bigger than $size.
     * 
     * The autoloader has to look into every file. Large files
     * like images may result in exceeding the max_execution_time.
     * 
     * Default is set to 1MB. A size of 0 would disable this limitation.
     * 
     * @param int $size Size in bytes
     * @see $skipFilesize
     */
    public function setSkipFilesize($size) {
        $this->skipFilesize = $size;
    }
	
	
}