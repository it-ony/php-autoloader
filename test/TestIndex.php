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

TestIndex::__static();


/**
 * AutoloaderIndex test cases.
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
class TestIndex extends PHPUnit_Framework_TestCase {
	
	
	const INDEX_DIRECTORY = 'index';
	
	
	static public function __static() {
	    if (! file_exists(self::getIndexDirectory())) {
            mkdir(self::getIndexDirectory());
            
        }
	}
	
	
	public function testGetDefaultSQLiteIndex() {
		$index = AutoloaderIndex_PDO::getSQLiteInstance();
		$this->initIndex($index);
	}
	
	
    /**
     * @dataProvider provideExistingClassesWithPaths
     */
    public function testGetPath(AutoloaderIndex $index, $class, $path) {
        $this->assertEquals($path, $index->getPath($class));
    }
    
    
	/**
     * @dataProvider provideIndexes
     * @expectedException AutoloaderException_Index_NotFound
     */
    public function testFailGetPath(AutoloaderIndex $index) {
        $index->getPath("ClassWhichDoesntExist" . uniqid());
    }
    
    
	/**
     * @dataProvider provideIndexes
     */
    public function testHasNotPath(AutoloaderIndex $index) {
        $this->assertFalse($index->hasPath("ClassWhichDoesntExist" . uniqid()));
    }
    
    
	/**
     * @dataProvider provideExistingClassesWithPaths
     */
    public function testHasPath(AutoloaderIndex $index, $class) {
        $this->assertTrue($index->hasPath($class));
    }
    
    
	/**
     * @dataProvider provideExistingClassesWithPaths
     */
    public function testUnsetPath(AutoloaderIndex $index, $class) {
    	$this->assertTrue($index->hasPath($class));
    	$path = $index->getPath($class);
    	$index->unsetPath($class);
        $this->assertFalse($index->hasPath($class));
        
        if ($index instanceof AutoloaderIndex_SerializedHashtable) {
        	$index = $this->getIndexFromPersistence($index);
        	$this->assertFalse($index->hasPath($class));
        	
        }
        $index->setPath($class, $path);
    }
    
    
    /**
     * @return Array array($index, $class, $path)
     */
    public function provideExistingClassesWithPaths() {
        $cases    = array();
        $classes  = array(
            "TestClassA" => "classes/TestClassA.php",
            "TestClassB" => "classes/TestClassB.php"
        );
        foreach ($classes as $class => $path) {
            // simple test with non persistent state
            foreach ($this->getIndexes() as $index) {
                $cases[] = array(
                    $index,
                    $class,
                    $path
                );
                $index->setPath($class, $path);
                
            }
            
            // test with persistent state
            foreach ($this->getPersistentIndexes() as $index) {
                $index->setPath($class, $path);
                $cases[] = array(
                    $this->getIndexFromPersistence($index),
                    $class,
                    $path
                );
                
            }
            
            // test both with persistent and non persistent state 
            foreach ($this->getPersistentIndexes() as $index) {
                $index->setPath($class, $path);
                $persistentIndex = $this->getIndexFromPersistence($index);
                
                $class2 = "{$class}_NonPersistent";
                $path2  = "{$path}/NonPersistent";
                $persistentIndex->setPath($class2, $path2);
                
                $cases[] = array(
                    $persistentIndex,
                    $class,
                    $path
                );
                $cases[] = array(
                    $persistentIndex,
                    $class2,
                    $path2
                );
                
            }
        }
        return $cases;
    }
    
	
    /**
     * @return Array array($index)
     */
    public function provideIndexes() {
        $cases = array();
        foreach ($this->getIndexes() as $index) {
            $cases[] = array($index);
            
        }
        foreach ($this->getIndexes() as $index) {
        	$index->setPath("AnyClass", "AnyPath");
            $cases[] = array($index);
            
        }
        foreach ($this->getPersistentIndexes() as $index) {
            $cases[] = array($this->getIndexFromPersistence($index));
            
        }
        foreach ($this->getPersistentIndexes() as $index) {
        	$index->setPath("AnyClass", "AnyPath");
            $cases[] = array($this->getIndexFromPersistence($index));
            
        }
        return $cases;
    }
	
	
    /**
     * @return AutoloaderIndex_SerializedHashtable_GZ
     */
    private function createAutoloaderIndex_SerializedHashtable_GZ() {
        $index = new AutoloaderIndex_SerializedHashtable_GZ();
        $this->initIndex($index);
        return $index;
    }
    
    
    /**
     * @return createAutoloaderIndex_PDO
     */
    private function createAutoloaderIndex_PDO(PDO $pdo) {
        $index = new AutoloaderIndex_PDO($pdo);
        $this->initIndex($index);
        return $index;
    }
    
    
    /**
     * @return createAutoloaderIndex_PDO
     */
    private function createAutoloaderIndex_PDO_SQLITE($filename = null) {
        $index = AutoloaderIndex_PDO::getSQLiteInstance($filename);
        $this->initIndex($index);
        return $index;
    }
    
    
    /**
     * @return createAutoloaderIndex_PDO
     */
    private function createAutoloaderIndex_PDO_MySQL() {
        $index = new AutoloaderIndex_PDO(new PDO("mysql:dbname=test"));
        $this->initIndex($index);
        return $index;
    }


    /**
     * @return AutoloaderIndex_IniFile
     */
    private function createAutoloaderIndex_IniFile() {
        $index = new AutoloaderIndex_IniFile();
        $this->initIndex($index);
        return $index;
    }


    /**
     * @return AutoloaderIndex_CSV
     */
    private function createAutoloaderIndex_CSV() {
        $index = new AutoloaderIndex_CSV();
        $this->initIndex($index);
        return $index;
    }


    /**
     * @return AutoloaderIndex_PHPArrayCode
     */
    private function createAutoloaderIndex_PHPArrayCode() {
        $index = new AutoloaderIndex_PHPArrayCode();
        $this->initIndex($index);
        return $index;
    }
    
    
    /**
     * @return AutoloaderIndex_SerializedHashtable
     */
    private function createAutoloaderIndex_SerializedHashtable() {
        $index = new AutoloaderIndex_SerializedHashtable();
        $this->initIndex($index);
        return $index;
    }
    
    
    /**
     * @return AutoloaderIndex_Dummy
     */
    private function createAutoloaderIndex_Dummy() {
        $index = new AutoloaderIndex_Dummy();
        $this->initIndex($index);
        return $index;
    }
    
    
    /**
     * @return AutoloaderIndex_SerializedHashtable
     */
    private function getIndexFromPersistence(AutoloaderIndex $index) {
    	if ($index instanceof AutoloaderIndex_File) {
	        $indexClass = get_class($index);
	        $indexPath  = $index->getIndexPath();
	        
	        // Index should save its state now
	        $index->__destruct();
	        unset($index);
	                
	        $index = new $indexClass();
	        $this->initIndex($index);
	        $index->setIndexPath($indexPath);
	        
    	}
        
        return $index;
    }
    
    
    private function initIndex(AutoloaderIndex $index) {
        $index->setAutoloader(new Autoloader());
        if ($index instanceof AutoloaderIndex_File) {
            $index->setIndexPath($this->getIndexFile());
            
        }
    }
    
    
    /**
     * @return Array
     */
    private function getIndexes() {
    	$indeces =  array(
            $this->createAutoloaderIndex_Dummy(),
            $this->createAutoloaderIndex_PHPArrayCode(),
            $this->createAutoloaderIndex_CSV(),
            $this->createAutoloaderIndex_IniFile(),
            $this->createAutoloaderIndex_SerializedHashtable(),
            $this->createAutoloaderIndex_SerializedHashtable_GZ(),
            $this->createAutoloaderIndex_PDO_SQLITE(tempnam(sys_get_temp_dir(), "PDOTest"))
        );
        
        try {
        	$indeces[] = $this->createAutoloaderIndex_PDO_MySQL();
        	
        } catch (PDOException $e) {
        	trigger_error($e->getMessage());
        	
        }
        
        return $indeces;
    }
    
    
    /**
     * @return Array
     */
    private function getPersistentIndexes() {
    	$indexes = array();
    	foreach ($this->getIndexes() as $index) {
            if ($index instanceof AutoloaderIndex_Dummy) {
                continue;
                    
            }
            $indexes[] = $index;
            
    	}
    	return $indexes;
    }
    
    
    /**
     * @return String
     */
    private function getIndexFile() {
        return self::getIndexDirectory() . DIRECTORY_SEPARATOR . uniqid();
    }
    
    
    /**
     * @return String
     */
    static public function getIndexDirectory() {
    	return dirname(__FILE__) . DIRECTORY_SEPARATOR
             . self::INDEX_DIRECTORY;
    }
	
	
}