<?php
/**
 * Autoloader test suite.
 * 
 * The "Exception thrown without a stack frame in Unknown on line 0"
 * is a side effect of the tearDown() which deletes the indexes, before
 * every destructor was called.
 * 
 * Copyright (C) 2010  Markus Malkusch <markus@malkusch.de>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package Autoloader
 * @subpackage test
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 */


require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . "/../Autoloader.php";


class AutoloaderSuite extends PHPUnit_Framework_TestSuite {

	
    public static function suite() {
        $suite = new self();
 
        $suite->addTestSuite("TestAutoloader");
        $suite->addTestSuite("TestIndex");
        $suite->addTestSuite("TestParser");
 
        return $suite;
    }

    
    public function tearDown() {
        $this->deleteDirectory(TestAutoloader::getClassDirectory());
        $this->deleteDirectory(TestIndex::getIndexDirectory());
    }
    
    
    private function deleteDirectory($directory) {
    	foreach (new DirectoryIterator($directory) as $file) {
    		if (in_array($file, array(".", ".."))) {
    			continue;
    			
    		}
    		$path = $directory . DIRECTORY_SEPARATOR . $file;
    		is_dir($path)
    		    ? $this->deleteDirectory($path)
    		    : unlink($path);
    		
    	}
    	rmdir($directory);
    }

    
}