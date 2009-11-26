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
	'AutoloaderIndex_SerializedHashtable',
    dirname(__FILE__).'/AutoloaderIndex_SerializedHashtable.php'
);
Autoloader::registerInternalClass(
    'AutoloaderException_Index_IO',
    dirname(__FILE__).'/exception/AutoloaderException_Index_IO.php'
);


/**
 * The index is a compressed serialized hashtable.
 * 
 * This index works similar to AutoloaderIndex_SerializedHashtable. Its only
 * difference is that the index file is compressed. In environments with
 * a hugh count of class definitions a plain text index file would produce
 * too much IO costs. 
 */
class AutoloaderIndex_SerializedHashtable_GZ extends AutoloaderIndex_SerializedHashtable {
	
	
	private
	/**
	 * @var int
	 */
	$compression = 1;
    
    
    /**
     * @return String
     */
    protected function readFile($file) {
    	$content = @gzfile($file);
    	if (! $content) {
    		throw new AutoloaderException_Index_IO($file);
    		
    	}
    	return implode('', $content);
    }
    
    
    /**
     * @return int written Bytes
     * @throws AutoloaderException_Index_IO
     */
    protected function saveFile($file, $serializedIndex) {
    	$zp = @gzopen($file, "w{$this->compression}");
    	if (! $zp) {
    		throw new AutoloaderException_Index_IO($file);
    		
    	}
    	$bytes = gzwrite($zp, $serializedIndex);
    	gzclose($zp);
    	return $bytes;
    }


}