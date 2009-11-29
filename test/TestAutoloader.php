<?php
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


require_once dirname(__FILE__) . "/../Autoloader.php";

TestAutoloader::__static();


class TestAutoloader extends PHPUnit_Framework_TestCase {
	
	
	const CLASS_DIRECTORY = "testClasses";
	
	
    static public function __static() {
        if (! file_exists(self::getClassDirectory())) {
            mkdir(self::getClassDirectory());
            
        }
    }
    
    
    /**
     * @dataProvider provideTestExceptionsOnLoadingFailure
     */
    public function testExceptionsOnLoadingFailure($class, $missingClass) {
    	try {
    	   $object = new $class();
    	   $this->fail("Expecting AutoloaderException_SearchFailed for class $class.");
    	   
    	} catch (AutoloaderException_SearchFailed $e) {
    		// expected
    		$this->assertEquals($missingClass, $e->getClass());
    		
    	}
    }
	
	
	public function testDefaultInstance() {
		require_once dirname(__FILE__) . "/../Autoloader.php";
		$this->assertNotNull(Autoloader::getDefaultInstance());
	}
	
	
	public function testRemovedDefaultPath() {
		$autoloader = new Autoloader();
		$autoloader->removeGuessedPath();
		
		$this->assertTrue(count($autoloader->getPaths()) == 0);
	}
	
	
	/**
	 * @dataProvider provideTestClassPath
	 */
	public function testClassPath(Autoloader $autoloader, $expectedPath) {
		foreach ($autoloader->getPaths() as $path) {
			if (realpath($path) == $expectedPath) {
				return;
				
			}
		}
		$this->fail("$expectedPath not found. Paths:\n" . implode("\n", $autoloader->getPaths()));
	}
	
	
	/**
	 * @dataProvider provideClassNames
	 */
	public function testLoadClass($class) {
		$this->assertLoadable($class);
	}
	
	
	public function testFailLoadClass() {
		$this->assertNotLoadable("ClassDoesNotExist");
	}
	
	
	/**
	 * @dataProvider provideTestSkipPatterns
	 */
	public function testSkipPatterns($class) {
		$this->assertNotLoadable($class);
	}
	
	
	public function testUnregisterAutoloader() {
		$class = $this->makeClass("TestUnregisterAutoloader", "testUnregisterAutoloader");
		
		Autoloader::getDefaultInstance()->remove();
		$this->assertNotLoadable($class);
		
		Autoloader::getDefaultInstance()->register();
		$this->assertLoadable($class);
	}
	
	
	public function testDifferentClassPaths() {
		$pathA = "testDifferentClassPathsA";
		$pathB = "testDifferentClassPathsB";
		
		$classA = $this->makeClass("A", $pathA); 
		$classB = $this->makeClass("B", $pathB); 
		
		$tempLoader = new Autoloader();
		$tempLoader->removeGuessedPath();
		$tempLoader->addPath(self::getClassDirectory() . "/" . $pathA);
		$tempLoader->addPath(self::getClassDirectory() . "/" . $pathB);

		Autoloader::getDefaultInstance()->remove();
		$this->assertNotLoadable($classA);
		$this->assertNotLoadable($classB);
		
		$tempLoader->register();
		$this->assertLoadable($classA);
        $this->assertLoadable($classB);
        
		$tempLoader->remove();
		Autoloader::getDefaultInstance()->register();
	}
	
	
	public function testGetRegisteredAutoloaders() {
		$autoloaders = array();
        $autoloaders[] = Autoloader::getDefaultInstance();
        
        $newAutoloader = new Autoloader();
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
	
	
    /**
     */
    public function testRemoveAllAutoloaders() {
        $registeredAutoloaders = Autoloader::getRegisteredAutoloaders();
        
        $autoloader = new Autoloader();
        $autoloader->register();
        
        $autoloader = new Autoloader();
        $autoloader->register();
        
        $this->assertGreaterThanOrEqual(2, count(Autoloader::getRegisteredAutoloaders()));
        
        Autoloader::removeAll();
        
        $this->assertEquals(0, count(Autoloader::getRegisteredAutoloaders()));
        
        
        foreach ($registeredAutoloaders as $autoloader) {
        	$autoloader->register();
        	
        }
    }
	
	
	/**
	 */
	public function testSeveralRequiredAutoloaders() {
		$autoloaders = Autoloader::getRegisteredAutoloaders();
		Autoloader::getDefaultInstance()->removeAllPaths();
		Autoloader::removeAll();
		
		$autoloaderPath = dirname(__FILE__) . "/../Autoloader.php";
		
		$classA   = $this->makeClass("A",         "a");
		$classA2  = $this->makeClass("A2",        "a");
		$requireA = $this->makeClass("requireA",  "a", "<?php require '$autoloaderPath' ?>");
		
		$classB   = $this->makeClass("B",         "b");
		$requireB = $this->makeClass("requireB",  "b", "<?php require '$autoloaderPath' ?>");
		
		
		$this->assertNotLoadable($classA);
		$this->assertNotLoadable($classA2);
		
		require self::getClassDirectory() . DIRECTORY_SEPARATOR
		      . "a" . DIRECTORY_SEPARATOR . "$requireA.test.php";
		      
		$this->assertLoadable($classA);
		$this->assertNotLoadable($classB);
		
		require self::getClassDirectory() . DIRECTORY_SEPARATOR
              . "b" . DIRECTORY_SEPARATOR . "$requireB.test.php";
              
        $this->assertLoadable($classA);              
        $this->assertLoadable($classA2);              
        $this->assertLoadable($classB);

        Autoloader::getDefaultInstance()->removeAllPaths();
        Autoloader::removeAll();
		
        foreach ($autoloaders as $autoloader) {
            $autoloader->register();
            
        }
	}
	
	
	/**
	 * @return Array
	 */
	public function provideTestExceptionsOnLoadingFailure() {
		$classes = array();
		
		$missingParentClass = uniqid("class");
		$classWithoutParent = $this->makeClass(
		  "child",
		  "provideTestExceptionsOnLoadingFailure",
		  "<?php class %name% extends $missingParentClass {}"
		);
		$classes[] = array($classWithoutParent, $missingParentClass);
		
		
		$missingClass = uniqid("class");
		$classes[] = array($missingClass, $missingClass);
		
		return $classes;
	}
	
	
	/**
	 * @return Array
	 */
	public function provideTestSkipPatterns() {
		Autoloader::getDefaultInstance()->addSkipPattern("~testPattern~");
		
		
		$classSVN  = $this->makeClass("SVN",    ".svn");
		$classCVS  = $this->makeClass("CVS",    ".CVS");
		$classTEST = $this->makeClass("TESt",   "testPattern");
		
		
		return array(
		    array($classSVN),
		    array($classCVS),
		    array($classTEST)
		);
	}
	
	
	/**
	 * @return Array
	 */
	public function provideClassNames() {
		$classes = array();
		$classes[] = $this->makeClass("TestA",     "");
		$classes[] = $this->makeClass("TestB",     "");
		$classes[] = $this->makeClass("TestC1",    "c");
		$classes[] = $this->makeClass("TestC2",    "c");
		$classes[] = $this->makeClass("TestD",     "d");
		$classes[] = $this->makeClass("TestE",     "e");
		$classes[] = $this->makeClass("TestF1",    "e/f");
		$classes[] = $this->makeClass("TestF2",    "e/f");
		
		$classes[] = $this->makeClass("TestInterface", "g", "<?php interface %name%{}?>");
		$classes[] = $this->makeClass("TestAbstract", "g", "<?php abstract class %name%{}?>");
		$classes[] = $this->makeClass("TestG1", "g", "<?php\nclass %name% {\n}?>");
		$classes[] = $this->makeClass("TestG2", "g", "<?php\n class %name% {\n}?>");
		$classes[] = $this->makeClass("TestG3", "g", "<?php\nclass %name%\n {\n}?>");
		$classes[] = $this->makeClass("TestG4", "g", "<?php\nclass %name% \n {\n}?>");
		$classes[] = $this->makeClass("TestG5", "g", "<?php\nClass %name% \n {\n}?>");
		$classes[] = $this->makeClass("TestG6", "g", "<?php\nclass %name% \n {\n}?>");
		
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
		$defaultLoader->addCallersPath();
		
		$loaderWithMorePaths = new Autoloader();
		$loaderWithMorePaths->addCallersPath();
		$loaderWithMorePaths->addPath(dirname(__FILE__) . "/../");
		
		$outsidePath = self::getClassDirectory(); 
		$loaderWithOutsideOfThisPath = new Autoloader();
		$loaderWithOutsideOfThisPath->removeGuessedPath();
		$loaderWithOutsideOfThisPath->addPath($outsidePath);
		
		return array(
		    array(Autoloader::getDefaultInstance(),   $autoPath),
		    array($defaultLoader,                     $autoPath),
		    array($loaderWithMorePaths,               $autoPath),
		    array($loaderWithOutsideOfThisPath,       $outsidePath),
		);
	}
	
	
	private function makeClass($name, $directory, $definition = "<?php class %name%{}?>") {
		$name     .= uniqid();
 		$directory = self::getClassDirectory() . DIRECTORY_SEPARATOR . $directory;
		$path      = $directory . DIRECTORY_SEPARATOR . "$name.test.php";
		
		if (file_exists($path)) {
			return $name;
			
		}
		
		if (! file_exists($directory)) {
		    mkdir($directory, 0777, true);
		    
		}
		$definition = str_replace("%name%", $name, $definition);
		file_put_contents($path, $definition);
		
		return $name;
	}
	
	
	private function assertLoadable($class) {
		try {
		    new ReflectionClass($class);
		  
		} catch (ReflectionException $e) {
			$this->fail("class $class is not loadable.");
			
		}
	}
	
	
	private function assertNotLoadable($class) {
		try {
		    new ReflectionClass($class);
		    new $class();
		    $this->fail("class $class is loadable.");
		  
		} catch (AutoloaderException_SearchFailed $e) {
			// expected
			
		} catch (ReflectionException $e) {
            // expected
		}
	}
	
	
    /**
     * @return String
     */
    static public function getClassDirectory() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR
             . self::CLASS_DIRECTORY;
    }


}