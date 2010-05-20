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


    static public
    /**
     * @var String
     */
    $testClassConstructorState = '';
	
	
	private
	/**
	 * @var AutoloaderTestHelper
	 */
	$autoloaderTestHelper;


    /**
     * testDeprecatedClassConstructor() tests the deprecated class constructor
     * __static(). The autoloader loads the class $class and the test expects
     * from the autoloader, that it sets the value of $testClassConstructorState
     * to $expectedState. Additionally an E_USER_DEPRECATED warning is expected.
     *
     * @param String $expectedState The class constructor sets this state
     * @param String $class         A class with a deprecated class constructor
     *
     * @dataProvider provideTestDeprecatedClassConstructor
     * @see $testClassConstructorState
     * @return void
     */
    public function testDeprecatedClassConstructor($expectedState, $class)
    {
        self::$testClassConstructorState = '';
        @$this->autoloaderTestHelper->assertLoadable($class);
        $lastError = error_get_last();

        $this->assertEquals($expectedState, self::$testClassConstructorState);
        $this->assertEquals(E_USER_DEPRECATED, $lastError['type']);
    }

    /**
     * provideTestDeprecatedClassConstructor() provide test cases for
     * testDeprecatedClassConstructor().
     *
     * A test case is an expected state and a not loaded class with a
     * deprecated class constructor. The class constructor should set the
     * value of $testClassConstructorState to the expected state.
     *
     * @see testDeprecatedClassConstructor()
     * @see $testClassConstructorState
     * @return Array
     */
    public function provideTestDeprecatedClassConstructor()
    {
        $this->autoloaderTestHelper = new AutoloaderTestHelper($this);

        return array(
            array(
                'da',
                $this->autoloaderTestHelper->makeClass(
                    'test',
                    '',
                    '<?php class %name% {

                        static public function __static() {
                            TestAutoloader::$testClassConstructorState = "da";
                        }
                    } ?>'
                )
            ),

            array(
                '',
                $this->autoloaderTestHelper->makeClass(
                    'test',
                    '',
                    '<?php class %name% {

                        public function __static() {
                            TestAutoloader::$testClassConstructorState = "db";
                        }
                    } ?>'
                )
            ),

            array(
                'dc',
                $this->autoloaderTestHelper->makeClassInNamespace(
                    'de\malkusch\autoloader\test',
                    'test',
                    '',
                    '<?php
                        namespace %namespace%;

                        class %name% {

                            static public function __static() {
                                \TestAutoloader::$testClassConstructorState = "dc";
                            }
                        }
                    ?>'
                )
            )
        );
    }


    /**
     * @dataProvider provideTestClassConstructor
     */
    public function testClassConstructor($expectedState, $class) {
        self::$testClassConstructorState = '';
        $this->autoloaderTestHelper->assertLoadable($class);
        $this->assertEquals($expectedState, self::$testClassConstructorState);
    }


    public function provideTestClassConstructor() {
        $this->autoloaderTestHelper = new AutoloaderTestHelper($this);
        
        return array(
            array(
                'a',
                $this->autoloaderTestHelper->makeClass(
                    'test',
                    '',
                    '<?php class %name% {

                        static public function classConstructor() {
                            TestAutoloader::$testClassConstructorState = "a";
                        }
                    } ?>'
                )
            ),

            array(
                '',
                $this->autoloaderTestHelper->makeClass(
                    'test',
                    '',
                    '<?php class %name% {

                        public function classConstructor() {
                            TestAutoloader::$testClassConstructorState = "b";
                        }
                    } ?>'
                )
            ),

            array(
                'c',
                $this->autoloaderTestHelper->makeClassInNamespace(
                    'de\malkusch\autoloader\test',
                    'test',
                    '',
                    '<?php
                        namespace %namespace%;
                        
                        class %name% {

                            static public function classConstructor() {
                                \TestAutoloader::$testClassConstructorState = "c";
                            }
                        }
                    ?>'
                )
            )
        );
    }


    /**
     * @dataProvider provideTestBuildIndex
     */
    public function testBuildIndex(Autoloader $autoloader, $expectedPaths) {
        $autoloader->buildIndex();
        $foundPaths = $autoloader->getIndex()->getPaths();
        ksort($foundPaths);
        ksort($expectedPaths);

        $this->assertEquals($expectedPaths, $foundPaths);
    }


    public function provideTestBuildIndex() {
        $cases      = array();
        $testHelper = new AutoloaderTestHelper($this);


        $testHelper->deleteDirectory('testBuildIndex/');
        $classes = array(
            $testHelper->makeClass('Test', 'testBuildIndex/'),
            $testHelper->makeClass('Test', 'testBuildIndex/'),
            $testHelper->makeClass('Test', 'testBuildIndex/A'),
            $testHelper->makeClass('Test', 'testBuildIndex/A'),
            $testHelper->makeClass('Test', 'testBuildIndex/A'),
            $testHelper->makeClass('Test', 'testBuildIndex/A/B'),
            $testHelper->makeClass('Test', 'testBuildIndex/A/B'),
            $testHelper->makeClass('Test', 'testBuildIndex/A/C'),
            $testHelper->makeClass('Test', 'testBuildIndex/A/C'),
            $testHelper->makeClass('Test', 'testBuildIndex/D'),
            $testHelper->makeClass('Test', 'testBuildIndex/D/E'),
        );
        $cases[] = array(
            new Autoloader($testHelper->getClassDirectory('testBuildIndex')),
            $this->getPaths($classes, $testHelper));


        $testHelper->deleteDirectory('testBuildIndex2/');
        $classes = array(
            $testHelper->makeClass('Test', 'testBuildIndex2/'),
        );
        $cases[] = array(
            new Autoloader($testHelper->getClassDirectory('testBuildIndex2')),
            $this->getPaths($classes, $testHelper));


        $testHelper->deleteDirectory('testBuildIndex3/');
        $classes = array(
            $testHelper->makeClass('Test', 'testBuildIndex3/'),
            $testHelper->makeClass('Test', 'testBuildIndex3/'),
        );
        $cases[] = array(
            new Autoloader($testHelper->getClassDirectory('testBuildIndex3')),
            $this->getPaths($classes, $testHelper));


        $testHelper->deleteDirectory('testBuildIndex4/');
        $classes = array(
            $testHelper->makeClass('Test', 'testBuildIndex4/A/B'),
            $testHelper->makeClass('Test', 'testBuildIndex4/B/C'),
        );
        $cases[] = array(
            new Autoloader($testHelper->getClassDirectory('testBuildIndex4')),
            $this->getPaths($classes, $testHelper));


        return $cases;
    }


    private function getPaths(Array $testClasses, AutoloaderTestHelper $testHelper) {
        $paths = array();
        foreach ($testClasses as $class) {
            $paths[$class] = realpath($testHelper->getGeneratedClassPath($class));

        }
        return $paths;
    }


    /**
     * Building an index fails if class definitions are not unique.
     *
     * @dataProvider provideTestFailBuildIndex
     * @expectedException AutoloaderException_IndexBuildCollision
     */
    public function testFailBuildIndex(Autoloader $autoloader) {
        $autoloader->buildIndex();
    }


    public function provideTestFailBuildIndex() {
        $cases = array();

        $definition = "<?php class XXXTest".uniqid()." {} ?>";

        $testHelper = new AutoloaderTestHelper($this);
        $testHelper->makeClass('Test', 'testFailBuildIndexA/');
        $testHelper->makeClass('Test', 'testFailBuildIndexA/', $definition);
        $testHelper->makeClass('Test', 'testFailBuildIndexA/', $definition);
        $cases[] = array(new Autoloader(
            $testHelper->getClassDirectory('testFailBuildIndexA')));

        $testHelper = new AutoloaderTestHelper($this);
        $testHelper->makeClass('Test', 'testFailBuildIndexB/A', $definition);
        $testHelper->makeClass('Test', 'testFailBuildIndexB/A');
        $testHelper->makeClass('Test', 'testFailBuildIndexB/B', $definition);
        $testHelper->makeClass('Test', 'testFailBuildIndexB/B');
        $cases[] = array(new Autoloader(
            $testHelper->getClassDirectory('testFailBuildIndexB')));

        return $cases;
    }


    /**
     * In this case you have several packages of this autoloader.
     * This might happen if you use libraries which come with this
     * autoloader in their own class path. The Autoloader should
     * define its classes only once no matter how often it is required
     * and where it has class definitions.
     */
    public function testRequire_onceMultipleAutoloaders() {
        $copyPath   = '/var/tmp/' . __FUNCTION__;
        $sourcePath = dirname(__FILE__) . "/..";
        `cp -r --link $sourcePath $copyPath`;

        require dirname(__FILE__) . "/../Autoloader.php";
        require "$copyPath/Autoloader.php";

        `rm -rf $copyPath`;
    }

	
	
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

        $classes[] = $this->autoloaderTestHelper->makeClassInNamespace("a",     "Test", "");
        $classes[] = $this->autoloaderTestHelper->makeClassInNamespace("a\b",   "Test", "");
        $classes[] = $this->autoloaderTestHelper->makeClassInNamespace("a\b",   "Test", "");
        $classes[] = $this->autoloaderTestHelper->makeClassInNamespace("a\b\c", "Test", "");

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