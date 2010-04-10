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


/*
 * These classes needed to be required in any case.
 */
require_once dirname(__FILE__) . '/Autoloader.php';


/**
 * This Autoloader is only for profiling during development of the
 * Autoloaderpackage itself used.
 * 
 * @package autoloader
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 */
class Autoloader_Profiler extends Autoloader {
    
    
    private
    /**
     * @var Array
     */
    $searchedClasses = array();


    protected function searchPath($class) {
        $this->searchedClasses[] = $class;
        return parent::searchPath($class);
    }


    /**
     * @return Array
     */
    public function getSearchedClasses() {
        return $this->searchedClasses;
    }


    public function buildIndex($class) {
        $this->normalizeClass($class);
        if ($this->index->hasPath($class)) {
            return;

        }
        $this->index->setPath($class, parent::searchPath($class));
    }
    
    
}