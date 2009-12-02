#! /usr/bin/php
<?php
##########################################################################
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


require dirname(__FILE__) . "/../Autoloader.php";


AutoloaderBenchmark::__static();


class AutoloaderBenchmark {
	
	
	private static
    /**
     * @var Array
     */
    $pdoPool = array();
    
	
	private
	/**
	 * @var int
	 */
	$getPathCount = 0,
	/**
	 * @var array
	 */
	$durations = array(),
	/**
	 * @var int
	 */
	$iterations = 0,
	/**
	 * @var int
	 */
	$indexSize = 0,
	/**
	 * @var String
	 */
	$hashtable,
	/**
	 * @var String
	 */
	$hashtableGZ,
	/**
	 * @var String
	 */
	$sqliteFile;
	
	
	static public function __static() {
		$cases = array(
            array(10,    1),
            array(100,   1),
            array(1000,  1),
            array(10000, 1),
            
            array(10,    10),
            array(100,   10),
            array(1000,  10),
            array(10000, 10),
            
            array(100,   100),
            array(1000,  100),
            array(10000, 100),
            
            array(1000,  1000),
            array(10000, 1000),
            
            array(10000, 10000),
            
            
		);
		
		foreach ($cases as $case) {
			$benchmark = new self($case[0], $case[1]);
	        $benchmark->run();
	        echo $benchmark;
	        
		}
	}
	
	
	public function __construct($indexSize, $getPathCount, $iterations = 10000) {
		$this->indexSize      = $indexSize;
        $this->iterations     = $iterations;
        $this->getPathCount   = $getPathCount;
		$this->sqliteFile     = tempnam("/var/tmp/", "AutoloaderBenchmarkSQLite_");
		$this->hashtable      = tempnam("/var/tmp/", "AutoloaderBenchmarkHT_");
		$this->hashtableGZ    = tempnam("/var/tmp/", "AutoloaderBenchmarkHT_GZ_");
		
		unlink($this->sqliteFile);
		unlink($this->hashtable);
		unlink($this->hashtableGZ);
	}
	
	
	public function run() {
		$indexes = $this->createIndexes();
		foreach ($indexes as $name => $index) {
			$this->fillIndex($index, $this->indexSize);
			
		}
		
		for ($i = 0; $i < $this->iterations; $i++) {
            foreach ($this->createIndexes() as $name => $index) {
            	if (! isset($this->durations[$name])) {
                    $this->durations[$name] = 0;
                    
            	}
				$classSet = $this->getClassSet();
				clearstatcache();
				
				$startTime = microtime(true);
				$this->runBenchmark($index, $classSet);
				$stopTime = microtime(true);
				
				$this->durations[$name] += $stopTime - $startTime;
				
			}
			
		}
		
		foreach ($indexes as $index) {
			$index->delete();
			
		}
		unlink($this->sqliteFile);
	}
	
	
	protected function runBenchmark(AutoloaderIndex $index, Array $classSet) {
		foreach ($classSet as $classNumber) {
			$index->getPath($this->getIndexedClassName($classNumber));
			
		}
	}
	
	
	/**
	 * @return Array
	 */
	private function getClassSet() {
		$allClasses = array();
		$classes    = array();
		for ($i = 0; $i < $this->indexSize; $i++) {
			$allClasses[] = $i;
			
		}
		for ($i = 0; $i < $this->getPathCount; $i++) {
			$index = (int) mt_rand(0, count($allClasses) - 1);
			$classes[] = $allClasses[$index];
			unset($allClasses[$index]);
			$allClasses = array_values($allClasses);
			
		}
		return $classes;
	}
	
	
	public function __toString() {
		$durations = "";
		foreach ($this->durations as $name => $duration) {
			$paddedName = str_pad($name . ":", 12);
			$durations  .= "$paddedName {$this->getAverageDuration($name)}\n"; 
			
		}
		return "==================================\n"
		     . "Index size:      $this->indexSize\n"
		     . "getPath() count: $this->getPathCount\n"
		     . "Iterations:      $this->iterations\n"
		     . "----------------------------------\n"
		     . "$durations\n"
		     . "==================================\n"; 
	}
	
	
	/**
	 * @return Array
	 */
	public function getDurations() {
		return $this->durations;
	}
	
	
	/**
	 * @return Array
	 */
	public function getAverageDurations() {
		$durations = array();
		foreach ($this->durations as $name => $duration) {
			$durations[$name] = $duration / $this->iterations;
			
		}
		return $durations;
	}
	
	
	/**
	 * @return float
	 */
	public function getAverageDuration($name) {
		$durations = $this->getAverageDurations();
		return $durations[$name];
	}
	
	
	/**
	 * @return Array
	 */
	private function createIndexes() {
		try {
            self::$pdoPool['mysql'] = new PDO("mysql:dbname=test");
            
		} catch (PDOException $e) {
			/*
			 * This happens when too many connections are open.
			 * We will reuse the last connection.
			 */
			
		}
		
		
		$indexes = array(
            "sqlite"      => AutoloaderIndex_PDO::getSQLiteInstance($this->sqliteFile),
            "mysql"       => new AutoloaderIndex_PDO(self::$pdoPool['mysql']),
            "hashtable"   => new AutoloaderIndex_SerializedHashtable(),
            "hashtableGZ" => new AutoloaderIndex_SerializedHashtable_GZ()
        );
        $indexes["hashtable"]->setIndexPath($this->hashtable);
        $indexes["hashtableGZ"]->setIndexPath($this->hashtableGZ);
        
        foreach ($indexes as $index) {
        	Autoloader::getDefaultInstance()->setIndex($index);
        	
        }
        
        return $indexes;
	}
	
	
	private function fillIndex(AutoloaderIndex $index, $count) {
		for ($i = 0; $i < $count; $i++) {
			$index->setPath($this->getIndexedClassName($i), uniqid());
			
		}
		$index->save();
	}
	
	
	/**
	 * @param int $count
	 * @return String
	 */
	private function getIndexedClassName($count) {
		return "class$count";
	}

	
}
