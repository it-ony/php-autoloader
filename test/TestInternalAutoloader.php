<?php
##########################################################################
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


require_once dirname(__FILE__) . "/../Autoloader.php";


/**
 * InternalAutoloader test cases.
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
class TestInternalAutoloader extends PHPUnit_Framework_TestCase {

	
	/**
     * @param String $class
     * @param String $path
     * @dataProvider provideTestAutoload
     */
    public function testAutoload($class, $path) {
        $autoloaderTestHelper = new AutoloaderTestHelper($this);
        
        Autoloader::removeAll();
        
        $autoloaderTestHelper->assertNotLoadable($class);
        InternalAutoloader::getInstance()->registerClass($class, $path);
        $autoloaderTestHelper->assertLoadable($class);
    }
    
	
	public function testGetInstance() {
		$this->assertTrue(InternalAutoloader::getInstance() instanceof InternalAutoloader);
		$this->assertTrue(InternalAutoloader::getInstance()->isRegistered());
	}
	
	
	public function testSingleton() {
		$this->assertEquals(1, count(InternalAutoloader::getRegisteredAutoloaders()));
	}
	
	
	public function testRemoveAll() {
		InternalAutoloader::removeAll();
		$this->assertRemoved();
	}
	
	
	public function testRemove() {
		InternalAutoloader::getInstance()->remove();
		$this->assertRemoved();
	}
	

	/**
	 * @return array
	 */
	public function provideTestAutoload() {
		$autoloaderTestHelper = new AutoloaderTestHelper($this);
		$return               = array();
		
		$class    = $autoloaderTestHelper->makeClass("ClassA", 'testInternal');
		$return[] = array(
            $class,
            $autoloaderTestHelper->getGeneratedClassPath($class)
        );
		$class    = $autoloaderTestHelper->makeClass("ClassA2", 'testInternal');
		$return[] = array(
            $class,
            $autoloaderTestHelper->getGeneratedClassPath($class)
        );
		$class    = $autoloaderTestHelper->makeClass("ClassB", 'testInternal/sub');
		$return[] = array(
            $class,
            $autoloaderTestHelper->getGeneratedClassPath($class)
        );
		
		return $return;
	}

    
    private function assertRemoved() {
        $this->assertFalse(InternalAutoloader::getInstance()->isRegistered());
        $this->assertEquals(0, count(InternalAutoloader::getRegisteredAutoloaders()));
        
        InternalAutoloader::getInstance()->register();
        $this->assertTrue(InternalAutoloader::getInstance()->isRegistered());
    }
    
    
}