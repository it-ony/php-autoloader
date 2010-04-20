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
 * Implementation of missing PHP5 functions
 * 
 * Some required functions are not present in older PHP 5 APIs. This
 * class will check their existence and implement them if needed.
 * 
 * @package autoloader
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 * @version 1.0
 */
class OldPHPAPI {
	
	
	public function checkAPI() {
		$this->sys_get_temp_dir();
		$this->error_get_last();
		$this->parse_ini_string();
		$this->str_getcsv();
	}
	
	
	private function error_get_last() {
		if (function_exists(__FUNCTION__)) {
			return;
			
		}
		function error_get_last() {
			return array(
                'type'      => 0,
                'message'   => 'Getting the last error message is not supported by your old PHP version.',
                'file'      => '/dev/null',
                'line'      => 0
			);
		}
	}
	
	
	private function sys_get_temp_dir() {
		if (function_exists(__FUNCTION__)) {
			return;
			
		}
		function sys_get_temp_dir() {
			$envVars = array('TMP', 'TEMP', 'TMPDIR');
			foreach ($envVars as $envVar) {
				$temp = getenv($envVar);
				if (! empty($temp)) {
					return $temp;
					
				}
				
			}
			
			
            $temp = tempnam(__FILE__, '');
            if (file_exists($temp)) {
                unlink($temp);
                return dirname($temp);
                
            }
            throw new LogicException("sys_get_temp_dir() failed.");
		}
	}


    /**
     * @see AutoloaderIndex_IniFile
     */
    private function parse_ini_string() {
        if (function_exists(__FUNCTION__)) {
			return;

		}
        function parse_ini_string($data) {
            $file = tempnam(sys_get_temp_dir(), 'parse_ini_string');
            file_put_contents($file, $data);
            $iniData = parse_ini_file($file);
            unlink($file);
            return $iniData;
        }
    }


    /**
     * @see AutoloaderIndex_CSV
     */
    private function str_getcsv() {
        if (function_exists(__FUNCTION__)) {
			return;

		}
        function str_getcsv($data) {
            $fp = tmpfile();
            fwrite($fp, $data);
            fseek($fp, 0);
            $csv = fgetcsv($fp);
            fclose($fp);
            return $csv;
        }
    }
	
	
}