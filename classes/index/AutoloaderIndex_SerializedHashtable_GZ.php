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
	'AutoloaderIndex_SerializedHashtable',
    dirname(__FILE__).'/AutoloaderIndex_SerializedHashtable.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index_IO',
    dirname(__FILE__).'/exception/AutoloaderException_Index_IO.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Index_IO_FileNotExists',
    dirname(__FILE__).'/exception/AutoloaderException_Index_IO_FileNotExists.php'
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
     * @throws AutoloaderException_Index_IO_FileNotExists
     * @throws AutoloaderException_Index_IO
     */
    protected function readFile($file) {
    	$content = @gzfile($file);
    	if (! $content) {
    		if (! file_exists($file)) {
    			throw new AutoloaderException_Index_IO_FileNotExists($file);
    			
    		}
    		
    		$error = error_get_last();
    		
    		if (! @file_get_contents($file)) {
    			throw new AutoloaderException_Index_IO("Could not read '$file': $error[message]");
    			
    		} else {
    			throw new AutoloaderException_Index_IO("Could not decompress '$file': $error[message]");
    			
    		}
    	}
    	return implode('', $content);
    }
    
    
    /**
     * @return int written Bytes
     * @throws AutoloaderException_Index_IO
     */
    protected function saveFile($file, $data) {
    	$zp = @gzopen($file, "w{$this->compression}");
    	if (! $zp) {
    		$error = error_get_last();
    		throw new AutoloaderException_Index_IO("Could not write to $file: $error[message]");
    		
    	}
    	$bytes = gzwrite($zp, $data);
    	if (! @gzclose($zp)) {
    		$error = error_get_last();
            throw new AutoloaderException_Index_IO("Could not close $file: $error[message]");
    		
    	}
    	return $bytes;
    }


}