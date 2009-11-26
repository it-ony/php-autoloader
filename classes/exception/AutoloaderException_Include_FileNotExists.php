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
	'AutoloaderException_Include',
    dirname(__FILE__).'/AutoloaderException_Include.php'
);


/**
 * The required class definition does not exist.
 * 
 * This can happen if the Autoloader tries to load a class definition from
 * a stale index. Normally the Autoloader reacts on this exception to find
 * the new class definition. 
 */
class AutoloaderException_Include_FileNotExists extends AutoloaderException_Include {
    
    
}