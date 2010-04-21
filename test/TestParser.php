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
		$file = tempnam(sys_get_temp_dir(), "AutoloaderTestParser");
		$this->assertTrue((bool) file_put_contents($file, $source));
		$this->assertEquals($source, file_get_contents($file));
		$this->assertTrue($parser->isClassInFile($class, $file));
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
				$provider[] = array_merge($parser, $source);
				
			}
		}
		return $provider;
	}
	
	
	/**
     * @return Array
     */
	public function provideSource() {
		return array(
            array("Test", "<?php interface Test{}?>"),
            array("Test", "<?php interface teSt{}?>"),
            array("Test", "<?php abstract class Test{}?>"),
	        array("Test", "<?php\nclass Test{\n}?>"),
	        array("Test", "<?php\n class Test {\n}?>"),
	        array("Test", "<?php\nclass Test\n {\n}?>"),
	        array("Test", "<?php\nclass Test \n {\n}?>"),
	        array("Test", "<?php\nClass Test \n {\n}?>"),
	        array("Test", "<?php\nclass Test \n {\n}?>"),
	        array("Test", "<?php\nclass Test1 \n {\n}\nclass Test \n {\n} ?>")
        );
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