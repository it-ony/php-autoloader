<?php
/**
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
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 */


/**
 * These classes are needed by the Autoloader itself.
 * They have to be registered statically.
 * 
 * @see Autoloader::registerInternalClass()
 */
Autoloader::registerInternalClass(
	'AutoloaderIndex',
    dirname(__FILE__).'/index/AutoloaderIndex.php'
);
Autoloader::registerInternalClass(
	'AutoloaderException',
    dirname(__FILE__).'/exception/AutoloaderException.php'
);
Autoloader::registerInternalClass(
	'AutoloaderException_SearchFailed',
    dirname(__FILE__).'/exception/AutoloaderException_SearchFailed.php'
);
Autoloader::registerInternalClass(
	'AutoloaderException_SearchFailed_EmptyClassPath',
    dirname(__FILE__).'/exception/AutoloaderException_SearchFailed_EmptyClassPath.php'
);
Autoloader::registerInternalClass(
	'AutoloaderException_Include',
    dirname(__FILE__).'/exception/AutoloaderException_Include.php'
);
Autoloader::registerInternalClass(
	'AutoloaderException_Include_FileNotExists',
    dirname(__FILE__).'/exception/AutoloaderException_Include_FileNotExists.php'
);
Autoloader::registerInternalClass(
	'AutoloaderException_Include_ClassNotDefined',
    dirname(__FILE__).'/exception/AutoloaderException_Include_ClassNotDefined.php'
);
Autoloader::registerInternalClass(
	'AutoloaderException_Index_NotDefined',
    dirname(__FILE__).'/index/exception/AutoloaderException_Index_NotDefined.php'
);
Autoloader::registerInternalClass(
	'AutoloaderException_GuessPathFailed',
    dirname(__FILE__).'/index/exception/AutoloaderException_GuessPathFailed.php'
);
Autoloader::registerInternalClass(
	'AutoloaderIndex_Dummy',
    dirname(__FILE__).'/index/AutoloaderIndex_Dummy.php'
);
Autoloader::registerInternalClass(
	'AutoloaderIndex_PDO',
    dirname(__FILE__).'/index/AutoloaderIndex_PDO.php'
);
Autoloader::registerInternalClass(
	'AutoloaderIndex_SerializedHashtable',
    dirname(__FILE__).'/index/AutoloaderIndex_SerializedHashtable.php'
);
Autoloader::registerInternalClass(
    'AutoloaderIndex_SerializedHashtable_GZ',
    dirname(__FILE__).'/index/AutoloaderIndex_SerializedHashtable_GZ.php'
);
Autoloader::registerInternalClass(
    'AutoloaderFileParser',
    dirname(__FILE__).'/parser/AutoloaderFileParser.php'
);


/**
 * As a simple requiring of this file should be
 * enough to get this Autoloader working a new
 * instance of the Autoloader must be created.
 */
Autoloader::__static();


/**
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
 */
class Autoloader {
    
    
    const CLASS_CONSTRUCTOR = '__static';
    
    
    private static
    /**
     * @var Autoloader
     */
    $defaultInstance,
    /**
     * @var array
     */
    $internalClasses = array();
    
    
    private
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
    $guessedPath = '',
    /**
     * @var Array
     */
    $paths = array(),
    /**
     * @var AutoloaderIndex
     */
    $index,
    /**
     * @var AutoloaderFileParser
     */
    $parser;
    
    
    /**
     * A default Autoloader will be created.
     */
    static public function __static() {
    	self::$defaultInstance = new self();
    }
    
    
    /**
     * @return Autoloader
     */
    static public function getDefaultInstance() {
    	return self::$defaultInstance;
    }
    
    
    /**
     * This is used for internal classes, which cannot
     * use the Autoloader. They will be required in a 
     * traditional way without any index or searching.
     * 
     * @param String $class
     * @param String $path
     */
    static public function registerInternalClass($class, $path) {
        self::normalizeClass($class);
        self::$internalClasses[$class] = $path;
    }
    
    
    /**
     * @param String $class
     */
    static private function normalizeClass(& $class) {
        $class = strtolower($class);
    }
    
    
    /**
     * The best parser is the tokenizer, which will be used
     * as default.
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
     * This Autoloader will be removed from the stack.
     */
    public function remove() {
    	spl_autoload_unregister($this->getAutoloadCallback());
    }
    
    
    /**
     * This Autoloader will be registered at the stack.
     * If not set the index will be a AutoloaderIndex_SerializedHashtable_GZ
     * and the parser will be (if PHP has tokenizer support) 
     * AutoloaderFileParser_Tokenizer.
     * 
     * @throws AutoloaderException_GuessPathFailed
     */
    public function register() {
        // spl_autoload_register disables __autoload(). This might be unwanted.
        if (function_exists('__autoload')) {
            spl_autoload_register("__autoload");
        
        }
    	spl_autoload_register($this->getAutoloadCallback());
    	
    	// set the default index
    	if (empty($this->index)) {
            $this->setIndex(new AutoloaderIndex_SerializedHashtable_GZ());
            
    	}
    	
    	// set the default parser
    	if (empty($this->parser)) {
    		$this->setParser(AutoloaderFileParser::getInstance());
    		
    	}
    	
    	// guess the class path
    	if (empty($this->paths)) {
            $this->addCallersPath();
            
    	}
    }
    
    
    /**
     * @return Callback
     */
    private function getAutoloadCallback() {
    	return array($this, 'autoload');
    }
    
    
    /**
     * You might change the index if your not happy with
     * the default index.
     */
    public function setIndex(AutoloaderIndex $index) {
        $this->index = $index;
        $this->index->setAutoloader($this);
    }
    
    
    /**
     * You can define several class paths where the
     * Autoloader will search for classes.
     * 
     * @param String $path A class path
     */
    public function addPath($path) {
    	$path = realpath($path); 
        $this->paths[md5($path)] = $path;
    }
    
    
    /**
     * @param String $path A class path
     */
    public function removePath($path) {
    	$path = realpath($path); 
        unset($this->paths[md5($path)]);
    }
    
    
    /**
     * Adds the class path of the caller.
     * 
     * @see addPath()
     * @throws AutoloaderException_GuessPathFailed
     */
    public function addCallersPath() {
        $autoloaderPath = realpath(dirname(__FILE__) . '/..');
        foreach (debug_backtrace() as $trace) {
            $path = realpath(dirname($trace['file']));
            if ($path != $autoloaderPath) {
                $this->addPath($path);
                $this->guessedPath = $path;
                return;
                
            }
        }
        throw new AutoloaderException_GuessPathFailed();
    }
    
    
    /**
     * The constructor automatically adds a guessed path
     * where it assumes to find classes. If you're not happy
     * with this path, you might remove it from the class
     * path list.
     */
    public function removeGuessedPath() {
    	$this->removePath($this->guessedPath);
    }
    
    
    /**
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
     * The autoloader has to look into every file. Large files
     * like images may result in exceeding the max_execution_time.
     * A size of 0 would disable this limitation.
     * 
     * Default is set to 1MB.
     * 
     * @param int $size Size in bytes
     * @see $skipFilesize
     */
    public function setSkipFilesize($size) {
        $this->skipFilesize = $size;
    }
    
    
    /**
     * @return Array The class paths
     */
    public function getPaths() {
        return $this->paths;
    }
    
    
    /**
     * PHP will call this method for loading a class. 
     * 
     * @param String $class
     */
    public function autoload($class) {
        self::normalizeClass($class);
        
    	/**
         * spl_autoload_call() runs the complete stack,
         * even though the class is already defined by
         * a previously registered method.
         */
        if (class_exists($class, false)) {
            return;
        
        }
        
        try {
            if (array_key_exists($class, self::$internalClasses)) {
                $this->loadClass($class, self::$internalClasses[$class]);
                return;
                
            }
            
            if (empty($this->index)) {
                throw new AutoloaderException_Index_NotDefined();
                
            }
            
            if (! $this->index->hasPath($class)) {
                $path = $this->searchPath($class);
                $this->index->setPath($class, $path);
                
            }
            
            
            try {
                $path = $this->index->getPath($class);
                $this->loadClass($class, $path);
                
            } catch (AutoloaderException_Include $e) {
                $this->index->unsetPath($class);
                $path = $this->searchPath($class);
                $this->index->setPath($class, $path);
                $this->loadClass($class, $path);
                
            }
            
        } catch (AutoloaderException $exception) {
            if (! $this->handleErrors()) {
                return;
                
            }
            throw $exception;
            
        }
    }
    
    
    /**
     * @param String $class
     * @throws AutoloaderException
     * @throws AutoloaderException_SearchFailed
     * @throws AutoloaderException_SearchFailed_EmptyClassPath
     * @return String
     */
    private function searchPath($class) {
    	$caughtExceptions = array();
        foreach ($this->paths as $searchpath) {
        	try {
	            $directories = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($searchpath));
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
        		/**
        		 * An exception shouldn't stop the file search.
        		 * But if no files were found it could be thrown.
        		 */
        		$caughtExceptions[] = $e;
        		
        	}
        }
        
        
        if (empty($this->paths)) {
        	throw new AutoloaderException_SearchFailed_EmptyClassPath($class);
        	
        } elseif (! empty($caughtExceptions)) {
        	throw $caughtExceptions[0]; // just throw the first one
        	
        } else {
        	throw new AutoloaderException_SearchFailed($class);
            
        }
    }
    
    
    /**
     * @return bool If this autoloader is the last in the stack
     */
    private function handleErrors() {
        return array_search($this->getAutoloadCallback(), spl_autoload_functions())
           === count(spl_autoload_functions()) - 1;
    }
    
    
    /**
     * Includes the class definition and calls the class constructor.
     * 
     * @param String $class
     * @param String $path
     * @throws AutoloaderException_Include
     * @throws AutoloaderException_Include_FileNotExists
     * @throws AutoloaderException_Include_ClassNotDefined
     */
    private function loadClass($class, $path) {
        if (! @include_once $path) {
            if (! file_exists($path)) {
                throw new AutoloaderException_Include_FileNotExists($path);
                
            } else {
                throw new AutoloaderException_Include("Failed to include $path for $class.");
                
            }
        }
        
        if (! (class_exists($class, false) || interface_exists($class, false))) {
            throw new AutoloaderException_Include_ClassNotDefined($class);
            
        }
        
        try {
            $reflectionClass = new ReflectionClass($class);
            $static = $reflectionClass->getMethod(self::CLASS_CONSTRUCTOR);
            if ($static->isStatic() && $static->getDeclaringClass()->getName() == $reflectionClass->getName()) {
                eval($class.'::'.self::CLASS_CONSTRUCTOR.'();');
            
            }
            
        } catch (ReflectionException $e) {
            // No class constructor
            
        }
    }
    
    
}