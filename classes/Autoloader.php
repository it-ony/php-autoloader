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
 * @version 1.2
 */
class Autoloader extends AbstractAutoloader {
    
    
    private static
    /**
     * @var array
     */
    $internalClasses = array();
    
    
    private
    /**
     * @var int the time in seconds to find a class definition
     */
    $searchTimeoutSeconds = 0,
    /**
     * @var int Skip files greater than 1MB as default
     */
    $skipFilesize = 1048576,
    /**
     * @var Array ignore SVN, CVS and *.dist files
     */
    $skipPatterns = array(
    	'~/\.svn/~',
    	'~/\.CVS/~',
    	'~/\.dist$~i'
    ),
    /**
     * @var String
     */
    $path = '',
    /**
     * @var AutoloaderIndex
     */
    $index,
    /**
     * @var AutoloaderFileParser
     */
    $parser;

    
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
    	foreach (self::getRegisteredAutoloaders() as $autoloader) { //TODO use __CLASS__ in PHP 5.3 and remove the other implementations
    		$autoloader->remove();
    		
    	}
    }
    
    
    /**
     * Set the class path of the caller.
     * 
     * @throws AutoloaderException_GuessPathFailed
     */
    public function __construct() {
    	$this->setPath(self::getCallersPath());
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
     * -The index would be an AutoloaderIndex_SerializedHashtable_GZ
     * -The parser will be (if PHP has tokenizer support) an AutoloaderFileParser_Tokenizer
     * -The class path was set by the constructor to the directory of the calling file
     * -The timeout for finding a class is set to max_execution_time
     * 
     * {@link spl_autoload_register()} disables __autoload(). This might be
     * unwanted, so register() also adds __autoload() to the stack.
     * 
     * @throws AutoloaderException_GuessPathFailed
     * @see setIndex()
     * @see AutoloaderIndex_SerializedHashtable_GZ
     * @see setParser()
     * @see AutoloaderFileParser_Tokenizer
     * @see spl_autoload_register()
     */
    public function register() {
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

    	parent::register();
    	
    	self::normalizeSearchPaths();
    }
    
    
	/**
     * This Autoloader will be removed from the stack.
     * 
     * @see removeAll()
     */
    public function remove() {
    	//TODO A previously by normalizeSearchPaths() removed autoloader could now be active.
    	parent::remove();
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
        			$removalCandidate->remove();
        			
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
     */
    public function setPath($path) {
        $this->path = realpath($path);
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
     * Adds a regular expression for ignoring files in the class paths.
     * 
     * Files which paths match one of these patterns won't be
     * searched for class definitions.
     * 
     * This is useful for version control paths where files
     * with class definitions exists.
     * Subversion (.svn) and CVS (.CVS) are excluded by default.
     * 
     * @param String $pattern a regular expression including delimiters
     * @see $skipPatterns
     */
    public function addSkipPattern($pattern) {
        $this->skipPatterns[] = $pattern;
    }
    
    
    /**
     * Set a file size to ignore files bigger than $size.
     * 
     * The autoloader has to look into every file. Large files
     * like images may result in exceeding the max_execution_time.
     * 
     * Default is set to 1MB. A size of 0 would disable this limitation.
     * 
     * @param int $size Size in bytes
     * @see $skipFilesize
     */
    public function setSkipFilesize($size) {
        $this->skipFilesize = $size;
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
      		$path = $this->index->getPath($class);
            
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
    private function searchPath($class) {
    	set_time_limit($this->searchTimeoutSeconds);
    	
    	$caughtExceptions = array();
        try {
            $directories = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->path));
            foreach ($directories as $file) {
                
                $file = (string) $file;
                
                // skip defined path patterns
                foreach ($this->skipPatterns as $pattern) {
                	if (preg_match($pattern, $file)) {
                		continue 2;
                		
                	}
                	
                }
                
                if (! is_file($file)) {
                    continue;
                    
                }
                
                // avoid too large files
                if ($this->skipFilesize > 0 && filesize($file) > $this->skipFilesize) {
                	continue;
                	
                }
                
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

