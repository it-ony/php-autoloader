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


/**
 * When a path is read from an AutoloaderIndex, this filter is applied on
 * that path.
 *
 * @package autoloader
 * @subpackage index
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 * @version 1.0
 * @see AutoloaderIndex::addGetFilter()
 */
interface AutoloaderIndexGetFilter {


    /**
     * @param String $path
     * @return String
     * @see AutoloaderIndex::getPath()
     */
    public function filterGetPath($path);


}