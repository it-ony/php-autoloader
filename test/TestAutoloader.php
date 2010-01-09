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


require_once dirname(__FILE__) . "/../Autoloader.php";


/**
 * Autoloader test cases.
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
class TestAutoloader extends PHPUnit_Framework_TestCase {
	
	
	private
	/**
	 * @var AutoloaderTestHelper
	 */
	$autoloaderTestHelper;
	
	
	
    /**
     * This Test checks if a normalized Autolader will registered
     * again, after removing its parent Autoloader.
     */
    public function testReregisteringAfterRemoval() {
    	Autoloader::removeAll();
    	
    	
    	$classA = $this->autoloaderTestHelper->makeClass("A", "testReregisteringAfterRemoval");
        $classB = $this->autoloaderTestHelper->makeClass("B", "testReregisteringAfterRemoval/B");
    	
    	
        $autoloaderB = new Autoloader(AutoloaderTestHelper::getClassDirectory() . "/testReregisteringAfterRemoval/B");
        $autoloaderB->register();
        
        
        $this->assertTrue($autoloaderB->isRegistered());
        
        $autoloaderA = new Autoloader(AutoloaderTestHelper::getClassDirectory() . "/testReregisteringAfterRemoval");
        $autoloaderA->register();
        
        
        $this->assertTrue($autoloaderA->isRegistered());
        $this->assertFalse($autoloaderB->isRegistered());

        $autoloaderA->remove();
        
        $this->assertFalse($autoloaderA->isRegistered());
        $this->assertTrue($autoloaderB->isRegistered());
        
        $this->autoloaderTestHelper->assertNotLoadable($classA);
        $this->autoloaderTestHelper->assertLoadable($classB);
        
        Autoloader::removeAll();
    }
    
    
    public function testNormalizedClassPaths() {
    	$autoloader = Autoloader::getRegisteredAutoloader();
		Autoloader::removeAll();
    	
    	$classA = $this->autoloaderTestHelper->makeClass("A", "testNormalizedClassPaths");
    	$classB = $this->autoloaderTestHelper->makeClass("B", "testNormalizedClassPaths/B");
    	
    	$autoloaderA = new Autoloader(AutoloaderTestHelper::getClassDirectory() . "/testNormalizedClassPaths");
    	$autoloaderA->register();
    	
    	$autoloaderB = new Autoloader(AutoloaderTestHelper::getClassDirectory() . "/testNormalizedClassPaths/B");
    	$autoloaderB->register();
    	
    	$this->assertTrue($autoloaderA->isRegistered());
        $this->assertFalse($autoloaderB->isRegistered());
        $this->autoloaderTestHelper->assertLoadable($classA);
        $this->autoloaderTestHelper->assertLoadable($classB);
    	
    	Autoloader::removeAll();

    	
    	$classA = $this->autoloaderTestHelper->makeClass("A", "testNormalizedClassPaths");
    	$classB = $this->autoloaderTestHelper->makeClass("B", "testNormalizedClassPaths/B");
    	
    	
    	$autoloaderB = new Autoloader(AutoloaderTestHelper::getClassDirectory() . "/testNormalizedClassPaths/B");
    	$autoloaderB->register();
    	
    	$autoloaderA = new Autoloader(AutoloaderTestHelper::getClassDirectory() . "/testNormalizedClassPaths");
    	$autoloaderA->register();
    	
    	$this->autoloaderTestHelper->assertLoadable($classA);
    	$this->autoloaderTestHelper->assertLoadable($classB);
    	$this->assertTrue($autoloaderA->isRegistered());
        $this->assertFalse($autoloaderB->isRegistered());
    	
    	Autoloader::removeAll();
    	
    	$autoloader->register();
    }
	
	
	/**
	 * @dataProvider provideTestClassPath
	 */
	public function testClassPath(Autoloader $autoloader, $expectedPath) {
		$this->assertEquals(realpath($expectedPath), realpath($autoloader->getPath()));
	}
	
	
	/**
	 * @dataProvider provideClassNames
	 */
	public function testLoadClass($class) {
		$this->autoloaderTestHelper->assertLoadable($class);
	}
	
	
	public function testFailLoadClass() {
		$this->autoloaderTestHelper->assertNotLoadable("ClassDoesNotExist");
	}
	
	
	public function testGetRegisteredAutoloader() {
		$autoloader = Autoloader::getRegisteredAutoloader();
		$autoloader->remove();
		
		$autoloaders = array();
		
		$path = AutoloaderTestHelper::getClassDirectory() . "/testGetRegisteredAutoloaderA";
		@mkdir($path);
		@mkdir($path."/sub");
		$tmpAutoloader = new Autoloader($path);
		$tmpAutoloader->register();
		$autoloaders[] = $tmpAutoloader;
		
		$path = AutoloaderTestHelper::getClassDirectory() . "/testGetRegisteredAutoloaderB";
		@mkdir($path);
		@mkdir($path."/sub");
		$tmpAutoloader2 = new Autoloader($path);
		$tmpAutoloader2->register();
		$autoloaders[] = $tmpAutoloader2;
		
		foreach ($tmpAutoloader2 as $autoloader) {
			Autoloader::getRegisteredAutoloader($autoloader->getPath());
			Autoloader::getRegisteredAutoloader($autoloader->getPath()."/sub");
			
		}
		
		$tmpAutoloader->remove();
		$tmpAutoloader2->remove();
		
		$autoloader->register();
	}
	
	
	/**
	 * @expectedException AutoloaderException_PathNotRegistered
	 * @dataProvider provideTestGetRegisteredAutoloaderFailure
	 */
	public function testGetRegisteredAutoloaderFailure($path) {
		Autoloader::getRegisteredAutoloader($path);
	}
	
	
	/**
	 */
	public function testGetDefaultRegisteredAutoloaderFailure() {
		$autoloader = Autoloader::getRegisteredAutoloader();
		$autoloader->remove();
		$path = realpath(dirname(__FILE__));
		
		try {
			
			Autoloader::getRegisteredAutoloader();
			$this->fail("did not expect an Autoloader for $path.");
			
		} catch (AutoloaderException_PathNotRegistered $e) {
			$this->assertEquals($path, $e->getPath());
			
		}
		$autoloader->register();
	}
	
	
	public function testUnregisterAutoloader() {
		$class = $this->autoloaderTestHelper->makeClass("TestUnregisterAutoloader", "testUnregisterAutoloader");
		
		$autoloader = Autoloader::getRegisteredAutoloader();
		$autoloader->remove();
		$this->autoloaderTestHelper->assertNotLoadable($class);
		
		$autoloader->register();
		$this->autoloaderTestHelper->assertLoadable($class);
	}
	
	
	public function testDifferentClassPaths() {
		$pathA = "testDifferentClassPathsA";
		$pathB = "testDifferentClassPathsB";
		
		$classA = $this->autoloaderTestHelper->makeClass("A", $pathA); 
		$classB = $this->autoloaderTestHelper->makeClass("B", $pathB); 
		
		$defaultAutoloader = Autoloader::getRegisteredAutoloader();
		$defaultAutoloader->remove();
		
		$tempLoaderA = new Autoloader(AutoloaderTestHelper::getClassDirectory() . "/" . $pathA);
		$tempLoaderB = new Autoloader(AutoloaderTestHelper::getClassDirectory() . "/" . $pathB);

		$this->autoloaderTestHelper->assertNotLoadable($classA);
		$this->autoloaderTestHelper->assertNotLoadable($classB);
		
		$tempLoaderA->register();
		$tempLoaderB->register();
		$this->autoloaderTestHelper->assertLoadable($classA);
        $this->autoloaderTestHelper->assertLoadable($classB);
        
		$tempLoaderA->remove();
		$tempLoaderB->remove();
		$defaultAutoloader->register();
	}
	
	
	public function testGetRegisteredAutoloaders() {
		$autoloaders = array();
        $autoloaders[] = Autoloader::getRegisteredAutoloader();
        
        $newAutoloader = new Autoloader(sys_get_temp_dir());
        $newAutoloader->register();
        $autoloaders[] = $newAutoloader;
        
        foreach ($autoloaders as $expectedAutoloader) {
			foreach (Autoloader::getRegisteredAutoloaders() as $autoloader) {
				if ($autoloader === $expectedAutoloader) {
					continue 2;
					
				}
			}
            $this->fail("Autoloader wasn't registered.");
        }
        $newAutoloader->remove();
	}
	
	
	public function setUp() {
		$autoloader = new Autoloader();
		$autoloader->register();
		
		$this->autoloaderTestHelper = new AutoloaderTestHelper($this);
	}
	
	
	public function tearDown() {
		Autoloader::removeAll();
	}
	
	
    /**
     */
    public function testRemoveAllAutoloaders() {
        $registeredAutoloaders = Autoloader::getRegisteredAutoloaders();
        
        $autoloader = new Autoloader();
        $autoloader->register();
        
        $this->assertEquals(count($registeredAutoloaders), count(Autoloader::getRegisteredAutoloaders()));
        
        Autoloader::removeAll();
        
        $this->assertEquals(0, count(Autoloader::getRegisteredAutoloaders()));

        $autoloader = new Autoloader();
        $autoloader->register();
        
        $this->assertEquals(1, count(Autoloader::getRegisteredAutoloaders()));
        
        $autoloader = new Autoloader(sys_get_temp_dir());
        $autoloader->register();
        
        $this->assertEquals(2, count(Autoloader::getRegisteredAutoloaders()));
        
        Autoloader::removeAll();
        foreach ($registeredAutoloaders as $autoloader) {
        	$autoloader->register();
        	
        }
    }
	
	
	/**
	 */
	public function testSeveralRequiredAutoloaders() {
		$autoloaders = Autoloader::getRegisteredAutoloaders();
		Autoloader::removeAll();
		
		$autoloaderPath = dirname(__FILE__) . "/../Autoloader.php";
		
		$classA   = $this->autoloaderTestHelper->makeClass("A",         "a");
		$classA2  = $this->autoloaderTestHelper->makeClass("A2",        "a");
		$requireA = $this->autoloaderTestHelper->makeClass("requireA",  "a", "<?php require '$autoloaderPath' ?>");
		
		$classB   = $this->autoloaderTestHelper->makeClass("B",         "b");
		$requireB = $this->autoloaderTestHelper->makeClass("requireB",  "b", "<?php require '$autoloaderPath' ?>");
		
		
		$this->autoloaderTestHelper->assertNotLoadable($classA);
		$this->autoloaderTestHelper->assertNotLoadable($classA2);
		
		require AutoloaderTestHelper::getClassDirectory() . DIRECTORY_SEPARATOR
		      . "a" . DIRECTORY_SEPARATOR . "$requireA.test.php";
		      
		$this->autoloaderTestHelper->assertLoadable($classA);
		$this->autoloaderTestHelper->assertNotLoadable($classB);
		
		require AutoloaderTestHelper::getClassDirectory() . DIRECTORY_SEPARATOR
              . "b" . DIRECTORY_SEPARATOR . "$requireB.test.php";
              
        $this->autoloaderTestHelper->assertLoadable($classA);              
        $this->autoloaderTestHelper->assertLoadable($classA2);              
        $this->autoloaderTestHelper->assertLoadable($classB);

        Autoloader::removeAll();
		
        foreach ($autoloaders as $autoloader) {
            $autoloader->register();
            
        }
	}
	
	
	public function provideTestGetRegisteredAutoloaderFailure() {
		return array(array(sys_get_temp_dir()));
	}
	
	
	/**
	 * @return Array
	 */
	public function provideClassNames() {
		$this->autoloaderTestHelper = new AutoloaderTestHelper($this);
		
		$classes = array();
		$classes[] = $this->autoloaderTestHelper->makeClass("TestA",     "");
		$classes[] = $this->autoloaderTestHelper->makeClass("TestB",     "");
		$classes[] = $this->autoloaderTestHelper->makeClass("TestC1",    "c");
		$classes[] = $this->autoloaderTestHelper->makeClass("TestC2",    "c");
		$classes[] = $this->autoloaderTestHelper->makeClass("TestD",     "d");
		$classes[] = $this->autoloaderTestHelper->makeClass("TestE",     "e");
		$classes[] = $this->autoloaderTestHelper->makeClass("TestF1",    "e/f");
		$classes[] = $this->autoloaderTestHelper->makeClass("TestF2",    "e/f");
		
		$classes[] = $this->autoloaderTestHelper->makeClass("TestInterface", "g", "<?php interface %name%{}?>");
		$classes[] = $this->autoloaderTestHelper->makeClass("TestAbstract", "g", "<?php abstract class %name%{}?>");
		$classes[] = $this->autoloaderTestHelper->makeClass("TestG1", "g", "<?php\nclass %name% {\n}?>");
		$classes[] = $this->autoloaderTestHelper->makeClass("TestG2", "g", "<?php\n class %name% {\n}?>");
		$classes[] = $this->autoloaderTestHelper->makeClass("TestG3", "g", "<?php\nclass %name%\n {\n}?>");
		$classes[] = $this->autoloaderTestHelper->makeClass("TestG4", "g", "<?php\nclass %name% \n {\n}?>");
		$classes[] = $this->autoloaderTestHelper->makeClass("TestG5", "g", "<?php\nClass %name% \n {\n}?>");
		$classes[] = $this->autoloaderTestHelper->makeClass("TestG6", "g", "<?php\nclass %name% \n {\n}?>");
		
		$return = array();
		foreach ($classes as $class) {
			$return[] = array($class);
			
		}
		return $return;
	}
	
	
	/**
	 * @return Array
	 */
	public function provideTestClassPath() {
		require_once dirname(__FILE__) . "/../Autoloader.php";
		
		$autoPath = realpath(dirname(__FILE__));
		
		$defaultLoader = new Autoloader();
		
		$outsidePath = AutoloaderTestHelper::getClassDirectory(); 
		$loaderWithOutsideOfThisPath = new Autoloader($outsidePath);
		
		return array(
		    array($defaultLoader,                     $autoPath),
		    array($loaderWithOutsideOfThisPath,       $outsidePath),
		);
	}
	
	
}