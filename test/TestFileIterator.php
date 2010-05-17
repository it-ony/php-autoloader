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
        $autoloader = new Autoloader($path);
        $iterator->setAutoloader($autoloader);
        
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
        
        $cases[] = array(new AutoloaderFileIterator_Simple(),       $rootDir, $files);
        $cases[] = array(new AutoloaderFileIterator_SimpleCached(), $rootDir, $files);
        $cases[] = array(new AutoloaderFileIterator_PriorityList(), $rootDir, $files);
        
        return $cases;
    }
    
    
    /**
     * @dataProvider provideTestSkipPatterns
     */
    public function testSkipPatterns(AutoloaderFileIterator $iterator, Array $notExpectedFiles, $root) {
        $autoloader = new Autoloader($root);
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

        $simpleCachedIterator = new AutoloaderFileIterator_SimpleCached();
        $simpleCachedIterator->addSkipPattern('~myPattern1~');
        $simpleCachedIterator->addSkipPattern('~myPattern2~');
        
        $priorityIterator = new AutoloaderFileIterator_PriorityList();
        $priorityIterator->addSkipPattern('~myPattern1~');
        $priorityIterator->addSkipPattern('~myPattern2~');
        
        
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
        $cases[] = array(
            $simpleCachedIterator,
            $onlyIgnoredfiles,
            AutoloaderTestHelper::getClassDirectory("testSkipPatterns/onlyIgnored")
        );
        $cases[] = array(
            $simpleCachedIterator,
            $mixedfiles,
            AutoloaderTestHelper::getClassDirectory("testSkipPatterns/mixed")
        );
        $cases[] = array(
            $priorityIterator,
            $onlyIgnoredfiles,
            AutoloaderTestHelper::getClassDirectory("testSkipPatterns/onlyIgnored")
        );
        $cases[] = array(
            $priorityIterator,
            $mixedfiles,
            AutoloaderTestHelper::getClassDirectory("testSkipPatterns/mixed")
        );
        
        return $cases;
    }
    
    
    /**
     * @dataProvider provideTestEmptyIterator
     */
    public function testEmptyIterator(AutoloaderFileIterator $iterator, $root) {
        $autoloader = new Autoloader($root);
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
        
        $simpleCachedIterator = new AutoloaderFileIterator_SimpleCached();
        $simpleCachedIterator->addSkipPattern('~myPattern1~');
        $simpleCachedIterator->addSkipPattern('~myPattern2~');
        
        $priorityIterator = new AutoloaderFileIterator_PriorityList();
        $priorityIterator->addSkipPattern('~myPattern1~');
        $priorityIterator->addSkipPattern('~myPattern2~');
        
        $cases[] = array($simpleIterator,       AutoloaderTestHelper::getClassDirectory("testEmptyIterator/empty"));
        $cases[] = array($simpleIterator,       AutoloaderTestHelper::getClassDirectory("testEmptyIterator/onlyIgnored"));
        $cases[] = array($simpleCachedIterator, AutoloaderTestHelper::getClassDirectory("testEmptyIterator/empty"));
        $cases[] = array($simpleCachedIterator, AutoloaderTestHelper::getClassDirectory("testEmptyIterator/onlyIgnored"));
        $cases[] = array($priorityIterator,     AutoloaderTestHelper::getClassDirectory("testEmptyIterator/empty"));
        $cases[] = array($priorityIterator,     AutoloaderTestHelper::getClassDirectory("testEmptyIterator/onlyIgnored"));
        
        return $cases;
    }
    
    
    public function testPreferedPattern() {
        AutoloaderTestHelper::deleteDirectory("testPreferedPattern");
        $alTestHelper = new AutoloaderTestHelper($this);
        
        $alTestHelper->makeClass("A", "testPreferedPattern");
        touch(AutoloaderTestHelper::getClassDirectory("testPreferedPattern") . DIRECTORY_SEPARATOR . "B.inc");
        touch(AutoloaderTestHelper::getClassDirectory("testPreferedPattern") . DIRECTORY_SEPARATOR . "C.unimportant");
        $alTestHelper->makeClass("D", "testPreferedPattern");
        touch(AutoloaderTestHelper::getClassDirectory("testPreferedPattern") . DIRECTORY_SEPARATOR . "E.inc");
        touch(AutoloaderTestHelper::getClassDirectory("testPreferedPattern") . DIRECTORY_SEPARATOR . "F.unimportant");
        $alTestHelper->makeClass("G", "testPreferedPattern/sub");
        touch(AutoloaderTestHelper::getClassDirectory("testPreferedPattern/sub") . DIRECTORY_SEPARATOR . "H.inc");
        touch(AutoloaderTestHelper::getClassDirectory("testPreferedPattern/sub") . DIRECTORY_SEPARATOR . "I.unimportant");
        $alTestHelper->makeClass("J", "testPreferedPattern/sub");
        touch(AutoloaderTestHelper::getClassDirectory("testPreferedPattern/sub") . DIRECTORY_SEPARATOR . "K.inc");
        touch(AutoloaderTestHelper::getClassDirectory("testPreferedPattern/sub") . DIRECTORY_SEPARATOR . "L.unimportant");
        
        $iterator   = new AutoloaderFileIterator_PriorityList();
        $autoloader = new Autoloader(AutoloaderTestHelper::getClassDirectory("testPreferedPattern"));
        $iterator->setAutoloader($autoloader);
        
        $isUnimportantExpected = false;
        foreach ($iterator as $file) {
            if (! preg_match('~\.(inc|php)$~', $file)) {
                $isUnimportantExpected = true;
                
            } elseif ($isUnimportantExpected) {
                $this->fail("Did not expect the prefered file '$file'.");
                
            }
        }
    }
    
    
    /**
     * @dataProvider provideTestRepeatedIteratorUse
     * @param int $limit
     */
    public function testRepeatedIteratorUse(AutoloaderFileIterator $iterator, $limit = null) {
        $foundFiles = array();
        $i          = 0;
        foreach ($iterator as $file) {
            if (! is_null($limit) && $i >= $limit) {
                break;
                
            }
            $foundFiles[] = $file;
            
        }
        
        $this->assertEqualFoundFiles($foundFiles, $iterator, $limit);
        $this->assertEqualFoundFiles($foundFiles, $iterator, $limit);
        $this->assertEqualFoundFiles($foundFiles, $iterator, $limit);
    }
    
    
    private function assertEqualFoundFiles(Array $expectedFiles, AutoloaderFileIterator $iterator, $limit = null) {
        $i = 0;
        foreach ($iterator as $file) {
            if (! is_null($limit) && $i >= $limit) {
                break;
                
            }
            $this->assertEquals(array_shift($expectedFiles), $file);
            
        }
    }
    
    
    public function provideTestRepeatedIteratorUse() {
        AutoloaderTestHelper::deleteDirectory("testRepeatedIteratorUse");
        $alTestHelper = new AutoloaderTestHelper($this);
        
        
        $alTestHelper->makeClass("A", "testRepeatedIteratorUse");
        $alTestHelper->makeClass("B", "testRepeatedIteratorUse");
        $alTestHelper->makeClass("C", "testRepeatedIteratorUse/C");
        $alTestHelper->makeClass("D", "testRepeatedIteratorUse/D");
        $alTestHelper->makeClass("E", "testRepeatedIteratorUse/D");
        $alTestHelper->makeClass("F", "testRepeatedIteratorUse/D");
        $alTestHelper->makeClass("G", "testRepeatedIteratorUse/D/G");
        $alTestHelper->makeClass("H", "testRepeatedIteratorUse");
        
        $autoloader = new Autoloader(AutoloaderTestHelper::getClassDirectory("testRepeatedIteratorUse"));
        
        
        $simpleIterator = new AutoloaderFileIterator_Simple();
        $simpleIterator->setAutoloader($autoloader);
        
        $simpleCachedIterator = new AutoloaderFileIterator_SimpleCached();
        $simpleCachedIterator->setAutoloader($autoloader);
        
        $priorityIterator = new AutoloaderFileIterator_PriorityList();
        $priorityIterator->setAutoloader($autoloader);
        
        return array(
            array($simpleIterator, 1),
            array($simpleIterator, 3),
            array($simpleIterator, 0),
            array($simpleIterator),
            array($simpleIterator, 1),
            array($simpleIterator, 3),
            array($simpleIterator, 0),
            array($simpleCachedIterator, 1),
            array($simpleCachedIterator, 3),
            array($simpleCachedIterator, 0),
            array($simpleCachedIterator),
            array($simpleCachedIterator, 1),
            array($simpleCachedIterator, 3),
            array($simpleCachedIterator, 0),
            array($priorityIterator, 1),
            array($priorityIterator, 3),
            array($priorityIterator, 0),
            array($priorityIterator),
            array($priorityIterator, 1),
            array($priorityIterator, 3),
            array($priorityIterator, 0)
        );
    }
    
    
    /**
     * @dataProvider provideTestPriorityOrder
     */
    public function testPriorityOrder($rootDir, $priorizedName, $limit) {
        $autoloader       = new Autoloader(AutoloaderTestHelper::getClassDirectory($rootDir));
        $priorityIterator = new AutoloaderFileIterator_PriorityList();
        $priorityIterator->setAutoloader($autoloader);
        $priorityIterator->setClassname($priorizedName);
        
        $i = 0;
        foreach ($priorityIterator as $file) {
            $this->assertTrue((bool) preg_match("~$priorizedName~", basename($file)));
            $i++;
            if ($i >= $limit) {
                break;
                
            }
        }
    }
    
    
    public function provideTestPriorityOrder() {
        AutoloaderTestHelper::deleteDirectory("testPriorityOrder");
        $alTestHelper = new AutoloaderTestHelper($this);
        
        $alTestHelper->makeClass("anyClass",        "testPriorityOrder");
        $alTestHelper->makeClass("anyClass",        "testPriorityOrder");
        $alTestHelper->makeClass("anyClass",        "testPriorityOrder/C");
        $alTestHelper->makeClass("anyClass",        "testPriorityOrder/D");
        $alTestHelper->makeClass("anyClass",        "testPriorityOrder/D");
        $alTestHelper->makeClass("anyClass",        "testPriorityOrder/D");
        $alTestHelper->makeClass("priorityClass",   "testPriorityOrder/D/G");
        $alTestHelper->makeClass("priorityClass",   "testPriorityOrder");
        $alTestHelper->makeClass("otherClass",      "testPriorityOrder/D/G");
        $alTestHelper->makeClass("otherClass",      "testPriorityOrder");
        
        return array(
            array("testPriorityOrder", "priorityClass", 2),
            array("testPriorityOrder", "otherClass",    2)
        );
    }
    
    
    /**
     * @dataProvider provideTestLoadsOfFiles
     */
    public function testLoadsOfFiles(AutoloaderFileIterator $iterator, $rootDir) {
        $iterator->setAutoloader(new Autoloader(AutoloaderTestHelper::getClassDirectory($rootDir)));
        
        foreach ($iterator as $file) {
            
        }
    }
    
    
    public function provideTestLoadsOfFiles() {
        AutoloaderTestHelper::deleteDirectory("testLoadsOfFiles");
        $alTestHelper = new AutoloaderTestHelper($this);
        
        for ($i = 0; $i < 150; $i++) {
            $alTestHelper->makeClass("anyClass", "testLoadsOfFiles/flat");
            
        }
        
        for ($i = 0; $i < 150; $i++) {
            $alTestHelper->makeClass("anyClass", "testLoadsOfFiles" . str_repeat('/sub', $i));
            
        }
        
        return array(
            array(new AutoloaderFileIterator_PriorityList(),    "testLoadsOfFiles/flat"),
            array(new AutoloaderFileIterator_Simple(),          "testLoadsOfFiles/flat"),
            array(new AutoloaderFileIterator_SimpleCached(),    "testLoadsOfFiles/flat"),
            array(new AutoloaderFileIterator_PriorityList(),    "testLoadsOfFiles/sub"),
            array(new AutoloaderFileIterator_Simple(),          "testLoadsOfFiles/sub"),
            array(new AutoloaderFileIterator_SimpleCached(),    "testLoadsOfFiles/sub")
        );
    }

    
}