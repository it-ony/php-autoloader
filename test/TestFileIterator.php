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
    
    
    /**
     * @dataProvider provideTestSkipPatterns
     */
    public function testSkipPatterns(AutoloaderFileIterator $iterator, Array $notExpectedFiles, $root) {
        $autoloader = new Autoloader();
        $autoloader->setPath($root);
        $iterator->setAutoloader($autoloader);
        
        foreach ($notExpectedFiles as & $file) {
            $file = realpath($file);
            
        }
        $notExpectedFiles = array_flip($notExpectedFiles);
        foreach ($iterator as $file) {
            $this->assertFalse(
                array_key_exists(realpath($file), $notExpectedFiles),
                "should not find '$file'"
            );
            
        }
    }

    
    /**
     * @return Array
     */
    public function provideTestSkipPatterns() {
        AutoloaderTestHelper::deleteDirectory("testSkipPatterns");
        
        $alTestHelper       = new AutoloaderTestHelper($this);
        $cases              = array();
        $onlyIgnoredfiles   = array(
            $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("A", "testSkipPatterns/onlyIgnored/.CVS")),
            $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("B", "testSkipPatterns/onlyIgnored/.svn")),
            $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("C", "testSkipPatterns/onlyIgnored/.svn/C")),
            $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("D", "testSkipPatterns/onlyIgnored/myPattern1")),
            $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("myPattern2", "testSkipPatterns/onlyIgnored/")),
        );
        $mixedfiles = array(
            $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("A", "testSkipPatterns/mixed/.CVS")),
            $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("B", "testSkipPatterns/mixed/.svn")),
            $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("C", "testSkipPatterns/mixed/.svn/C")),
            $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("D", "testSkipPatterns/mixed/myPattern1")),
            $alTestHelper->getGeneratedClassPath($alTestHelper->makeClass("myPattern2", "testSkipPatterns/mixed/"))
        );
        $alTestHelper->makeClass("E", "testSkipPatterns/mixed/");
        $alTestHelper->makeClass("F", "testSkipPatterns/mixed/F");

        $simpleIterator = new AutoloaderFileIterator_Simple();
        $simpleIterator->addSkipPattern('~myPattern1~');
        $simpleIterator->addSkipPattern('~myPattern2~');
        
        
        $cases[] = array(
            $simpleIterator,
            $onlyIgnoredfiles,
            AutoloaderTestHelper::getClassDirectory("testSkipPatterns/onlyIgnored")
        );
        
        
        $cases[] = array(
            $simpleIterator,
            $mixedfiles,
            AutoloaderTestHelper::getClassDirectory("testSkipPatterns/mixed")
        );
        
        return $cases;
    }
    
    
    /**
     * @dataProvider provideTestEmptyIterator
     */
    public function testEmptyIterator(AutoloaderFileIterator $iterator, $root) {
        $autoloader = new Autoloader();
        $autoloader->setPath($root);
        $iterator->setAutoloader($autoloader);
        
        foreach ($iterator as $file) {
            $this->fail("Empty iterator expected but '$file' was found.");
            
        }
    }
    
    
    public function provideTestEmptyIterator() {
        AutoloaderTestHelper::deleteDirectory("testEmptyIterator");
        
        $alTestHelper       = new AutoloaderTestHelper($this);
        $cases              = array();
        
        $alTestHelper->makeClass("A", "testEmptyIterator/onlyIgnored/.CVS");
        $alTestHelper->makeClass("B", "testEmptyIterator/onlyIgnored/.svn");
        $alTestHelper->makeClass("C", "testEmptyIterator/onlyIgnored/.svn/C");
        $alTestHelper->makeClass("D", "testEmptyIterator/onlyIgnored/myPattern1");
        $alTestHelper->makeClass("myPattern2", "testEmptyIterator/onlyIgnored/");
        mkdir(AutoloaderTestHelper::getClassDirectory("testEmptyIterator/onlyIgnored/emptyDir"));
        
        mkdir(AutoloaderTestHelper::getClassDirectory("testEmptyIterator/empty"));
        
        $simpleIterator = new AutoloaderFileIterator_Simple();
        $simpleIterator->addSkipPattern('~myPattern1~');
        $simpleIterator->addSkipPattern('~myPattern2~');
        
        $cases[] = array($simpleIterator, AutoloaderTestHelper::getClassDirectory("testEmptyIterator/empty"));
        $cases[] = array($simpleIterator, AutoloaderTestHelper::getClassDirectory("testEmptyIterator/onlyIgnored"));
        
        return $cases;
    }

    
}