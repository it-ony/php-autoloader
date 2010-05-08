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


/*
 * These classes needed to be required in any case.
 */
require_once dirname(__FILE__).'/AbstractAutoloader.php';
require_once dirname(__FILE__).'/InternalAutoloader.php';
require_once dirname(__FILE__).'/exception/AutoloaderException.php';
require_once dirname(__FILE__).'/exception/AutoloaderException_PathNotRegistered.php';


/**
 * An implementation for Autoloading classes in PHP.
 * 
 * This Autoloader implementation searches recursivly in
 * defined class paths for a class definition.
 * 
 * Additionally it provides PHP with the class constructor
 * __static(). If a class has a public and static method
 * __static() the Autoloader will call this method.
 * 
 * Actually there's no need to define a class path with
 * Autoloader->addPath() as the constructor uses the path
 * of the debug_backtrace().
 * 
 * @package autoloader
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 * @version 1.8
 */
class Autoloader extends AbstractAutoloader {
    
    
    private static
    /**
     * @var array
     */
    $unregisteredNormalizedAutoloaders = array();


    protected
    /**
     * @var AutoloaderIndex
     */
    $index;
    
    
    private
    /**
     * @var int the time in seconds to find a class definition
     */
    $searchTimeoutSeconds = 0,
    /**
     * @var AutoloaderFileIterator
     */
    $fileIterator,
    /**
     * @var String
     */
    $path = '',
    /**
     * @var AutoloaderFileParser
     */
    $parser;

    
    /**
     * Sets a AutoloaderFileIterator.
     * 
     * This is not necessary to call, as the Autoloader initializes itself
     * with an AutoloaderFileIterator.
     */
    public function setFileIterator(AutoloaderFileIterator $fileIterator) {
    	$this->fileIterator = $fileIterator;
    	$this->fileIterator->setAutoloader($this);
    }
    
    
    /**
     * @return AutoloaderFileIterator
     */
    public function getFileIterator() {
    	return $this->fileIterator;
    }

    
    /**
     * Sets a AutoloaderFileParser.
     * 
     * This is not necessary to call, as the Autoloader initializes itself
     * with the best available parser.
     */
    public function setParser(AutoloaderFileParser $parser) {
    	$this->parser = $parser;
    }
    
    
    /**
     * @return AutoloaderFileParser
     */
    public function getParser() {
    	return $this->parser;
    }
    
    
    /**
     * @return AutoloaderIndex
     */
    public function getIndex() {
    	return $this->index;
    }
    
    
    /**
     * @param String $path
     * @return Autoloader
     * @see register
     * @see setPath
     * @throws AutoloaderException_PathNotRegistered
     */
    static public function getRegisteredAutoloader($path = null) {
    	$path = realpath(is_null($path) ? self::getCallersPath() : $path);
    	
    	foreach (self::getRegisteredAutoloaders() as $autoloader) {
    		if (strpos($path, $autoloader->getPath()) === 0) {
    			return $autoloader;
    			
    		}
    	}
    	throw new AutoloaderException_PathNotRegistered($path);
    }
    
    
	/**
     * @return Array all registered Autoloader instances which are doing their jobs
     * @see register()
     */
    static public function getRegisteredAutoloaders() {
    	$autoloaders = array();
    	foreach(parent::getRegisteredAutoloaders() as $autoloader) {
    		if ($autoloader instanceof self) {
    			$autoloaders[] = $autoloader;
    			
    		}
    	}
    	return $autoloaders;
    }
	
    
    /**
     * All instances of Autoloader will be removed from the stack.
     * 
     * @see remove()
     */
    static public function removeAll() {
    	self::$unregisteredNormalizedAutoloaders = array();
    	foreach (self::getRegisteredAutoloaders() as $autoloader) { //TODO use __CLASS__ in PHP 5.3 and remove the other implementations
    		$autoloader->remove();
    		
    	}
    }
    
    
    /**
     * Set the class path of the caller if $path is null.
     * 
     * @param String $path The class path
     * @throws AutoloaderException_GuessPathFailed
     * @throws AutoloaderException_ClassPath_NotExists
     * @throws AutoloaderException_ClassPath
     */
    public function __construct($path = null) {
    	$this->setPath(is_null($path) ? self::getCallersPath() : $path);
    }
    
    
    /**
     * @return String
     * @throws AutoloaderException_GuessPathFailed
     */
    static private function getCallersPath() {
    	$autoloaderPaths = array(
            realpath(dirname(__FILE__)),
            realpath(dirname(__FILE__) . '/..'),
        );
        foreach (debug_backtrace() as $trace) {
            $path = realpath(dirname($trace['file']));
            if (! in_array($path, $autoloaderPaths)) {
                return $path;
                
            }
        }
        throw new AutoloaderException_GuessPathFailed();
    }
    
    
    /**
     * This Autoloader will be registered at the stack.
     * 
     * After registration, this Autoloader is autoloading class definitions.
     * 
     * There is no need to configure this object. All missing
     * members are initialized before registration:
     * -The index would be an AutoloaderIndex_SerializedHashtable_GZ.
     * -The parser will be (if PHP has tokenizer support) an AutoloaderFileParser_Tokenizer.
     * -The class path was set by the constructor to the directory of the calling file.
     * -The timeout for finding a class is set to max_execution_time.
     * -The AutoloaderFileIterator searches for files in the filesystem.
     * 
     * {@link spl_autoload_register()} disables __autoload(). This might be
     * unwanted, so register() also adds __autoload() to the stack.
     * 
     * @throws AutoloaderException_GuessPathFailed
     * @see initMembers()
     * @see setIndex()
     * @see AutoloaderIndex_SerializedHashtable_GZ
     * @see setParser()
     * @see AutoloaderFileParser_Tokenizer
     * @see spl_autoload_register()
     */
    public function register() {
    	$this->initMembers();

    	parent::register();
    	
    	self::normalizeSearchPaths();
    }


    /**
     * This method builds an index in advance. You can use it to build
     * your index before deployment in a productive environment.
     *
     * The Autoloader does not have to be registered. All missing members
     * are initialized like in register().
     *
     * @throws AutoloaderException_IndexBuildCollision
     * @throws AutoloaderException_Index
     */
    public function buildIndex() {
        $this->initMembers();

        // The index should be clean before building
        try {
            $this->index->delete();

        } catch (AutoloaderException_Index $e) {
            // The index might not exist.

        }

        // All found classes are saved in the index
        foreach ($this->fileIterator as $file) {
            foreach ($this->parser->getClassesInFile($file) as $class) {
                // A collision throws an AutoloaderException_IndexBuildCollision.
                if ($this->index->hasPath($class)) {
                    throw new AutoloaderException_IndexBuildCollision(
                        $class,
                        array($this->index->getPath($class), $file));

                }
                $this->index->setPath($class, $file);

            }
        }
        $this->index->save();
    }


    /**
     * @see register()
     * @see buildIndex()
     */
    private function initMembers() {
        // set the default index
    	if (empty($this->index)) {
            $this->setIndex(new AutoloaderIndex_SerializedHashtable_GZ());

    	}

    	// set the default parser
    	if (empty($this->parser)) {
    		$this->setParser(AutoloaderFileParser::getInstance());

    	}

    	// set the timeout for finding a class to max_execution_time
    	if (empty($this->searchTimeoutSeconds)) {
    		$this->searchTimeoutSeconds = ini_get('max_execution_time');

    	}

    	// set the AutoloaderFileIterator
    	if (empty($this->fileIterator)) {
    	    $this->setFileIterator(new AutoloaderFileIterator_PriorityList());

    	}
    }
    
    
    /**
     * @see normalizeSearchPaths()
     * @see remove()
     */
    private function removeByNormalization() {
    	parent::remove();
    	
    	self::$unregisteredNormalizedAutoloaders[$this->getPath()] = $this;
    }
    
    
    
	/**
     * This Autoloader will be removed from the stack.
     * 
     * @see removeAll()
     */
    public function remove() {
    	parent::remove();
    	
    	$autoloaders = self::$unregisteredNormalizedAutoloaders;
    	self::$unregisteredNormalizedAutoloaders = array();
    	foreach ($autoloaders as $autoloader) {
    		$autoloader->register();
    		
    	}
    }
    
    
    /**
     * You might change the index if your not happy with
     * the default index AutoloaderIndex_SerializedHashtable_GZ.
     * 
     * @see AutoloaderIndex_SerializedHashtable_GZ
     */
    public function setIndex(AutoloaderIndex $index) {
        $this->index = $index;
        $this->index->setAutoloader($this);
    }
    
    
    /**
     * Paths which are below other search paths are removed. 
     * 
     * For example a /var/tmp would be removed if /var is already
     * a search path.
     */
    private static function normalizeSearchPaths() {
        foreach (self::getRegisteredAutoloaders() as $removalCandidate) {
        	foreach (self::getRegisteredAutoloaders() as $parentCandidate) {
        		$isIncluded =
                    strpos($removalCandidate->getPath(), $parentCandidate->getPath()) === 0
        		    && $removalCandidate !== $parentCandidate;
        		if ($isIncluded) {
        			$removalCandidate->removeByNormalization();
        			
        		}
        	}
        }
    }
    
    
    /**
     * You can define a class paths in which the Autoloader will search for classes.
     * 
     * The constructor did this automatically.
     * 
     * @param String $path A class path
     * @throws AutoloaderException_ClassPath_NotExists
     * @throws AutoloaderException_ClassPath
     */
    private function setPath($path) {
        $realpath = realpath($path);
        if (! $realpath) {
            if (! file_exists($path)) {
                throw new AutoloaderException_ClassPath_NotExists($path);
                
            }
            throw new AutoloaderException_ClassPath($path);
            
        }
        $this->path = $realpath;
    }
    
    
    /**
     * Get the search path of this autoloader.
     * 
     * @return String
     */
    public function getPath() {
    	return $this->path;
    }


    /**
     * Returns true if this instance is the first registered instance of
     * this class. It might return TRUE if there are Autoloaders registered
     * of different classes.
     *
     * @return bool
     * @see getAutoloaderPosition()
     * @throws AutoloaderException_PathNotRegistered
     */
    private function isFirstRegisteredInstance() {
        return $this->getAutoloaderPosition() == 0;
    }


    /**
     * Returns the position in the autoloader stack. The offset is 0.
     * It considers only instances of this class. That means a returned
     * position of 0 doesn't implie it is the first Autoloader in the
     * autoloader stack. It's only the first instance of this Autoloader class.
     *
     * @return int.
     * @throws AutoloaderException_PathNotRegistered
     */
    private function getAutoloaderPosition() {
        $position = array_search($this, self::getRegisteredAutoloaders());
        if ($position === false) {
            throw new AutoloaderException_PathNotRegistered($this->path);

        }
        return $position;
    }


    /**
     * @param String $class
     * @return String
     * @throws AutoloaderException_Index_NotFound
     */
    private function searchPathInIndexes($class) {
        // Only iterate once per __autoload call
        if (! $this->isFirstRegisteredInstance()) {
            throw new AutoloaderException_Index_NotFound($class);

        }
        foreach (self::getRegisteredAutoloaders() as $autoloader) {
            try {
                return $autoloader->getIndex()->getPath($class);

            } catch (AutoloaderException_Index_NotFound $e) {
                continue;

            }
        }
        throw new AutoloaderException_Index_NotFound($class);
    }
    
    
    /**
     * @param String $class
     * @throws AutoloaderException
     */
    protected function __autoload($class) {
        if (empty($this->index)) {
            throw new AutoloaderException_Index_NotDefined();
                
        }
            
        try {
      		$path = $this->searchPathInIndexes($class);
            
        } catch (AutoloaderException_Index_NotFound $e) {
            $path = $this->searchPath($class);
            $this->index->setPath($class, $path);
            
        }
            
        try {
            $this->loadClass($class, $path);
               
        } catch (AutoloaderException_Include $e) {
            $this->index->unsetPath($class);
            $path = $this->searchPath($class);
            $this->index->setPath($class, $path);
            $this->loadClass($class, $path);
                
        }
    }
    
    
    /**
     * find a class definition in the search paths
     * 
     * This methods resets the max_execution_time to $searchTimeoutSeconds.
     * 
     * @param String $class
     * @throws AutoloaderException
     * @throws AutoloaderException_SearchFailed
     * @see set_time_limit()
     * @return String
     */
    protected function searchPath($class) {
    	set_time_limit($this->searchTimeoutSeconds);
    	
    	$caughtExceptions = array();
        try {
            if ($this->fileIterator instanceof AutoloaderFileIterator_PriorityList) {
                $this->fileIterator->setClassname($class);
                
            }
            foreach ($this->fileIterator as $file) {
                if ($this->parser->isClassInFile($class, $file)) {
                	return $file;
                	
                }
            }
        } catch (AutoloaderException $e) {
        	/*
        	 * An exception shouldn't stop the file search.
        	 * But if no files were found it could be thrown.
        	 */
        	$caughtExceptions[] = $e;
        	
        }
        
        
        if (! empty($caughtExceptions)) {
        	throw $caughtExceptions[0]; // just throw the first one
        	
        } else {
        	throw new AutoloaderException_SearchFailed($class);
            
        }
    }
    
       
}


/*
 * These classes are needed by the Autoloader itself.
 * They have to be registered statically.
 */
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderIndex',
    dirname(__FILE__).'/index/AutoloaderIndex.php'
);
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderException_SearchFailed',
    dirname(__FILE__).'/exception/AutoloaderException_SearchFailed.php'
);
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderException_SearchFailed_EmptyClassPath',
    dirname(__FILE__).'/exception/AutoloaderException_SearchFailed_EmptyClassPath.php'
);
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderException_Include',
    dirname(__FILE__).'/exception/AutoloaderException_Include.php'
);
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderException_Include_FileNotExists',
    dirname(__FILE__).'/exception/AutoloaderException_Include_FileNotExists.php'
);
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderException_Include_ClassNotDefined',
    dirname(__FILE__).'/exception/AutoloaderException_Include_ClassNotDefined.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_ClassPath',
    dirname(__FILE__).'/exception/AutoloaderException_ClassPath.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_ClassPath_NotExists',
    dirname(__FILE__).'/exception/AutoloaderException_ClassPath_NotExists.php'
);
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderException_Index_NotDefined',
    dirname(__FILE__).'/index/exception/AutoloaderException_Index_NotDefined.php'
);
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderException_GuessPathFailed',
    dirname(__FILE__).'/index/exception/AutoloaderException_GuessPathFailed.php'
);
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderIndex_Dummy',
    dirname(__FILE__).'/index/AutoloaderIndex_Dummy.php'
);
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderIndex_PDO',
    dirname(__FILE__).'/index/AutoloaderIndex_PDO.php'
);
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderIndex_CSV',
    dirname(__FILE__).'/index/AutoloaderIndex_CSV.php'
);
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderIndex_IniFile',
    dirname(__FILE__).'/index/AutoloaderIndex_IniFile.php'
);
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderIndex_PHPArrayCode',
    dirname(__FILE__).'/index/AutoloaderIndex_PHPArrayCode.php'
);
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderIndex_SerializedHashtable',
    dirname(__FILE__).'/index/AutoloaderIndex_SerializedHashtable.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndex_SerializedHashtable_GZ',
    dirname(__FILE__).'/index/AutoloaderIndex_SerializedHashtable_GZ.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileParser',
    dirname(__FILE__).'/parser/AutoloaderFileParser.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileIterator_Simple',
    dirname(__FILE__).'/fileIterator/AutoloaderFileIterator_Simple.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileIterator_SimpleCached',
    dirname(__FILE__).'/fileIterator/AutoloaderFileIterator_SimpleCached.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderFileIterator_PriorityList',
    dirname(__FILE__).'/fileIterator/AutoloaderFileIterator_PriorityList.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_IndexBuildCollision',
    dirname(__FILE__).'/exception/AutoloaderException_IndexBuildCollision.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndexFilter_RelativePath',
    dirname(__FILE__).'/index/filter/AutoloaderIndexFilter_RelativePath.php'
);

