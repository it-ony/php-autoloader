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
	'AutoloaderIndexFilter',
    dirname(__FILE__).'/AutoloaderIndexFilter.php'
);
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderException_Index_Filter_RelativePath_InvalidBasePath',
    dirname(__FILE__).'/exception/AutoloaderException_Index_Filter_RelativePath_InvalidBasePath.php'
);


/**
 * @author Markus Malkusch <markus@malkusch.de>
 * @package autoloader
 * @subpackage index
 * @copyright Copyright (C) 2010 Markus Malkusch
 * @version 1.0
 */
class AutoloaderIndexFilter_RelativePath implements AutoloaderIndexFilter {


    private
    /**
     * @var String
     */
    $basePath = '',
    /**
     * @var String
     */
    $basePathArray = array();


    /**
     * @param String $basePath
     * @throws AutoloaderException_Index_Filter_RelativePath_InvalidBasePath
     */
    public function __construct($basePath = '') {
        if (empty($basePath)) {
            $root       = str_repeat(DIRECTORY_SEPARATOR . '..', 3);
            $basePath   = dirname(__FILE__) . $root;
            
        }
        $this->basePath = realpath($basePath);
        if ($this->basePath === false) {
            throw new AutoloaderException_Index_Filter_RelativePath_InvalidBasePath($basePath);

        }
        $this->basePathArray = explode(DIRECTORY_SEPARATOR, $this->basePath);
    }


    /**
     * @return String
     */
    public function getBasePath() {
        return $this->basePath;
    }


    /**
     * @param String $path
     * @return String
     * @see AutoloaderIndex::setPath()
     */
    public function filterSetPath($path) {
        $pathArray = explode(DIRECTORY_SEPARATOR, $path);
        foreach ($this->basePathArray as $level => $directory) {
            if ($pathArray[$level] !== $directory) {
                $level--;
                break;

            }
            unset($pathArray[$level]);
            
        }
        $prefix = str_repeat(
            '..' . DIRECTORY_SEPARATOR,
            count($this->basePathArray) - $level - 1
        );
        return $prefix . implode(DIRECTORY_SEPARATOR, $pathArray);
    }


    /**
     * @param String $path
     * @return String
     * @see AutoloaderIndex::setPath()
     */
    public function filterGetPath($path) {
        return $this->basePath . DIRECTORY_SEPARATOR . $path;
    }


}