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
    'AutoloaderException',
    dirname(__FILE__).'/AutoloaderException.php'
);


/**
 * This exception occurs during a collision while building the index.
 *
 * @see Autoloader::buildIndex()
 */
class AutoloaderException_IndexBuildCollision extends AutoloaderException {


    private
    /**
     * @var Array
     */
    $paths = array(),
    /**
     * @var String
     */
    $class = '';


    /**
     * @param String $class
     */
    public function __construct($class, array $paths) {
        parent::__construct(
            "class $class was defined in several files:". implode(', ', $paths));

        $this->class    = $class;
        $this->paths    = $paths;
    }


    /**
     * @return String
     */
    public function getClass() {
        return $this->class;
    }


    /**
     * @return Array
     */
    public function getPaths() {
        return $this->paths;
    }

	
}