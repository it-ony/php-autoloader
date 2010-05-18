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
require_once dirname(__FILE__) . "/../classes/Autoloader_Profiler.php";


/**
 * Autoloader_Profiler test cases.
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
class TestAutoloaderProfiler extends PHPUnit_Framework_TestCase {
	
	
	private
	/**
	 * @var AutoloaderTestHelper
	 */
	$autoloaderTestHelper;
	
	
    public function setUp() {
		$this->autoloaderTestHelper = new AutoloaderTestHelper($this);
        Autoloader::removeAll();
	}
	
	
	public function tearDown() {
	}


    public function testUseIndexInMultiALEnvironment() {
        $classA = $this->autoloaderTestHelper->makeClass('A', 'a');
        $classB = $this->autoloaderTestHelper->makeClass('B', 'b');

        $alA = new Autoloader_Profiler(
            dirname($this->autoloaderTestHelper->getGeneratedClassPath($classA)));
        $alA->register();
        $alA->addClassToIndex($classA);

        $alB = new Autoloader_Profiler(
            dirname($this->autoloaderTestHelper->getGeneratedClassPath($classB)));
        $alB->register();
        $alB->addClassToIndex($classB);

        $this->autoloaderTestHelper->assertLoadable($classA);
        $this->autoloaderTestHelper->assertLoadable($classB);

        $this->assertEquals(array(), $alA->getSearchedClasses());
        $this->assertEquals(array(), $alB->getSearchedClasses());
    }
	
	
}