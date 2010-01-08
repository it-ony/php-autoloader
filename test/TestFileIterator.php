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
 * AutoloaderFileIterator test cases.
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
class TestFileIterator extends PHPUnit_Framework_TestCase {
    
    
    /**
     * @param String $path
     * @dataProvider provideTestCompleteIteration
     */
    public function testCompleteIteration(AutoloaderFileIterator $iterator, $path, Array $expectedFiles) {
        $expectedFiles = array_flip($expectedFiles);
        foreach ($iterator as $file) {
            $file = realpath($file);
            $this->assertArrayHasKey($file, $expectedFiles);
            unset($expectedFiles[$file]);
            
        }
        $this->assertEquals(0, count($expectedFiles));
    }
    
    
    /**
     * @return Array
     */
    public function provideTestCompleteIteration() {
        $alTestHelper   = new AutoloaderTestHelper($this);
        $cases          = array();
        $rootDir        = $alTestHelper->getClassDirectory("testCompleteIteration");
        $files          = array(); 
        
        AutoloaderTestHelper::deleteDirectory("testCompleteIteration");
        
        $files[] = $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("A", "testCompleteIteration"));
        $files[] = $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("B", "testCompleteIteration"));
        $files[] = $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("C", "testCompleteIteration/C"));
        $files[] = $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("D", "testCompleteIteration/C"));
        $files[] = $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("E", "testCompleteIteration/E"));
        $files[] = $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("F", "testCompleteIteration/E/F"));
        $files[] = $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("G", "testCompleteIteration/C"));
        
        // ignored Files
        $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("I1", "testCompleteIteration/.CVS/"));
        $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("I2", "testCompleteIteration/.CVS/"));
        $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("I3", "testCompleteIteration/.CVS/test"));
        $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("I4", "testCompleteIteration/.svn"));
        touch(AutoloaderTestHelper::getClassDirectory("testCompleteIteration") . DIRECTORY_SEPARATOR . "test.dist");
        touch(AutoloaderTestHelper::getClassDirectory("testCompleteIteration") . DIRECTORY_SEPARATOR . "test.DIST");
        touch(AutoloaderTestHelper::getClassDirectory("testCompleteIteration") . DIRECTORY_SEPARATOR . "test.jpeg");
        touch(AutoloaderTestHelper::getClassDirectory("testCompleteIteration") . DIRECTORY_SEPARATOR . "test.jpg");
        touch(AutoloaderTestHelper::getClassDirectory("testCompleteIteration") . DIRECTORY_SEPARATOR . "test.gif");
        touch(AutoloaderTestHelper::getClassDirectory("testCompleteIteration") . DIRECTORY_SEPARATOR . "test.png");
        touch(AutoloaderTestHelper::getClassDirectory("testCompleteIteration") . DIRECTORY_SEPARATOR . "test.svg");
        touch(AutoloaderTestHelper::getClassDirectory("testCompleteIteration") . DIRECTORY_SEPARATOR . "test.ogm");
        touch(AutoloaderTestHelper::getClassDirectory("testCompleteIteration") . DIRECTORY_SEPARATOR . "test.ogg");
        touch(AutoloaderTestHelper::getClassDirectory("testCompleteIteration") . DIRECTORY_SEPARATOR . "test.mp3");
        touch(AutoloaderTestHelper::getClassDirectory("testCompleteIteration") . DIRECTORY_SEPARATOR . "test.wav");
        touch(AutoloaderTestHelper::getClassDirectory("testCompleteIteration") . DIRECTORY_SEPARATOR . "test.mpeg");
        touch(AutoloaderTestHelper::getClassDirectory("testCompleteIteration") . DIRECTORY_SEPARATOR . "test.mpg");
        
        $files[] = $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("H", "testCompleteIteration"));
        $files[] = $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("I", "testCompleteIteration"));
        $files[] = $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("J", "testCompleteIteration"));
        
        foreach ($files as & $file) {
            $file = realpath($file);
            
        }
        
        $simpleIterator = new AutoloaderFileIterator_Simple();
        $autoloader     = new Autoloader();
        $cases[]        = array($simpleIterator, $rootDir, $files);
        $simpleIterator->setAutoloader($autoloader);
        $autoloader->setPath($rootDir);
        
        return $cases;
    }

    
}