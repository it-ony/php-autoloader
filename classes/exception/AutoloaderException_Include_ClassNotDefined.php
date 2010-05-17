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


require_once dirname(__FILE__) . '/AutoloaderException_Include.php';


/**
 * The Autoloader throws this exception if the class could not be found.
 * 
 * Only if the Autoloader is the last Autoloader in the stack this exception
 * will be thrown out of the Autoloader context and lead to a termination.
 */
class AutoloaderException_Include_ClassNotDefined extends AutoloaderException_Include {
    
    
}