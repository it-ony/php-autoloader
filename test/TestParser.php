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
 * AutoloaderFileParser test cases.
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
class TestParser extends PHPUnit_Framework_TestCase {
	
	
	public function testAutoloaderUsesTokenizer() {
		$this->assertTrue(AutoloaderFileParser_Tokenizer::isSupported());
		
		$autoloader = new Autoloader();
		$autoloader->register();
		$autoloader->remove();
		
		$this->assertTrue(
		  $autoloader->getParser() instanceof AutoloaderFileParser_Tokenizer
        );
	}


    /**
     * @dataProvider provideTestGetClasses
     */
    public function testGetClassesInSource(AutoloaderFileParser $parser, Array $classes, $source) {
        $this->assertEquals($classes, $parser->getClassesInSource($source));
    }


    /**
     * @dataProvider provideTestGetClasses
     */
    public function testGetClassesInFile(AutoloaderFileParser $parser, Array $classes, $source) {
        $file = $this->createFile($source);
        $this->assertEquals($classes, $parser->getClassesInFile($file));
        unlink($file);
    }


	
	/**
	 * @dataProvider provideTestIsClassInSource
	 */
	public function testIsClassInSource(AutoloaderFileParser $parser, $class, $source) {
		$this->assertTrue($parser->isClassInSource($class, $source));
		$this->assertFalse($parser->isClassInSource($class.uniqid(), $source));
	}
	
	
	/**
	 * @dataProvider provideTestIsClassInSource
	 */
	public function testIsClassInFile(AutoloaderFileParser $parser, $class, $source) {
		$file = $this->createFile($source);
		$this->assertTrue(
            $parser->isClassInFile($class, $file),
            "$class not found in $file. These classes where found: "
            . print_r($parser->getClassesInSource($source), true)
        );
        $this->assertFalse($parser->isClassInSource($class.uniqid(), $file));
		unlink($file);
	}
	
	
	/**
	 * @return Array
	 */
	public function provideTestIsClassInSource() {
		$provider = array();
		foreach ($this->provideParser() as $parser) {
			foreach ($this->provideSource() as $source) {
                foreach ($source[0] as $class) {
                    $provider[] = array($parser[0], $class, $source[1]);

                }
			}
		}
		return $provider;
	}


	/**
	 * @return Array
	 */
	public function provideTestGetClasses() {
		$provider = array();
		foreach ($this->provideParser() as $parser) {
			foreach ($this->provideSource() as $source) {
                $provider[] = array($parser[0], $source[0], $source[1]);

			}
		}
		return $provider;
	}
	
	
	/**
     * @return Array
     */
	public function provideSource() {
		return array(
            array(array("Test"), "<?php interface Test{}?>"),
            array(array("teSt"), "<?php interface teSt{}?>"),
            array(array("Test"), "<?php abstract class Test{}?>"),
	        array(array("Test"), "<?php\nclass Test{\n}?>"),
	        array(array("Test"), "<?php\n class Test {\n}?>"),
	        array(array("Test"), "<?php\nclass Test\n {\n}?>"),
	        array(array("Test"), "<?php\nclass Test \n {\n}?>"),
	        array(array("Test"), "<?php\nClass Test \n {\n}?>"),
	        array(array("Test"), "<?php\nclass Test \n {\n}?>"),


	        array(
                array("Test1", "Test"),
                "<?php\nclass Test1 \n {\n}\nclass Test \n {\n} ?>"
            ),
	        array(
                array("Test1", "Test"),
                "<?php\nclass Test1 \n {\n}\interface Test \n {\n} ?>"
            ),
	        array(
                array("Test1", "Test"),
                "<?php\nabstract class Test1 \n {\n}\interface Test \n {\n} ?>"
            ),
	        array(
                array("Test1", "Test"),
                "<?php\ninterface Test1 \n {\n}\interface Test \n {\n} ?>"
            ),

            
            array(
                array(
                    'de\malkusch\autoloader\test\ns\bracket\Test1',
                    'de\malkusch\autoloader\test\ns\bracket\Test2'
                ),
                file_get_contents(__DIR__ . "/namespaceDefinitions/Bracket.php")
            ),
            array(
                array(
                    'de\malkusch\autoloader\test\ns\multibracket\A\Test1',
                    'de\malkusch\autoloader\test\ns\multibracket\A\Test2',
                    'de\malkusch\autoloader\test\ns\multibracket\B\Test1',
                    'de\malkusch\autoloader\test\ns\multibracket\B\Test2',
                ),
                file_get_contents(__DIR__ . "/namespaceDefinitions/MultiBracket.php")
            ),
            array(
                array(
                    'de\malkusch\autoloader\test\ns\multinobracket\A\Test1',
                    'de\malkusch\autoloader\test\ns\multinobracket\A\Test2',
                    'de\malkusch\autoloader\test\ns\multinobracket\B\Test1',
                    'de\malkusch\autoloader\test\ns\multinobracket\B\Test2',
                ),
                file_get_contents(__DIR__ . "/namespaceDefinitions/MultiNoBracket.php")
            ),
            array(
                array(
                    'de\malkusch\autoloader\test\ns\nobracket\Test1',
                    'de\malkusch\autoloader\test\ns\nobracket\Test2'
                ),
                file_get_contents(__DIR__ . "/namespaceDefinitions/NoBracket.php")
            )
        );
	}


    private function createFile($source) {
        $file = tempnam(sys_get_temp_dir(), "AutoloaderTestParser");
        $this->assertTrue((bool) file_put_contents($file, $source));
        return $file;
    }
	
	
	/**
	 * @return Array
	 */
	public function provideParser() {
		return array(
		    array(new AutoloaderFileParser_RegExp()),
		    array(new AutoloaderFileParser_Tokenizer())
		);
	}
	
	
}