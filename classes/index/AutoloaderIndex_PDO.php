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
 * @subpackage Index
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 */


Autoloader::registerInternalClass(
	'AutoloaderIndex',
    dirname(__FILE__).'/AutoloaderIndex.php'
);
Autoloader::registerInternalClass(
	'AutoloaderException_Index_NotFound',
    dirname(__FILE__).'/exception/AutoloaderException_Index_NotFound.php'
);


class AutoloaderIndex_PDO extends AutoloaderIndex {
    
	
	const DEFAULT_SQLITE = "AutoloaderIndex_PDO.sqlite.db";
	
    
    private
    /**
     * @var Array
     */
    $statements = array(),
    /**
     * @var PDO
     */
    $pdo;
    
    
    static public function getSQLiteInstance($filename = null) {
    	if (is_null($filename)) {
    		$filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::DEFAULT_SQLITE;
    		
    	}
    	$pdo = new PDO("sqlite://$filename");
    	return new self($pdo);
    }
    
    
    public function __construct(PDO $pdo) {
    	$this->pdo = $pdo;
    	
    	$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	try {
    		$stmt = $pdo->query("SELECT 1 FROM autoloadindex");
    		
    	} catch (PDOException $e) {
    		$pdo->exec("
    		    CREATE TABLE autoloadindex (
    		        context   CHAR(32),
    		        class     VARCHAR(255),
    		        path      VARCHAR(255),
    		        
    		        PRIMARY KEY (context, class)
    		    );
    		");
    		
    	}
    }
    
    
    /**
     * @return PDO
     */
    public function getPDO() {
    	return $this->pdo;
    }
    
    
    /**
     * @throws AutoloaderException_Index
     */
    public function delete() {
    	try {
    	   $this->pdo->exec("DROP TABLE autoloadindex");
    	   
    	} catch (PDOException $e) {
    		throw new AutoloaderException_Index($e->getMessage());
    		
    	}
    }
    
    
    /**
     * @param String $sql
     * @return PDOStatement
     */
    private function getStatement($sql) {
    	$key = md5($sql);
    	if (! array_key_exists($key, $this->statements)) {
    		$this->statements[$key] = $this->pdo->prepare($sql);
    		
    	}
    	return $this->statements[$key];
    }
    
    
    /**
     * @return String
     */
    private function getContext() {
    	return md5(implode("", $this->autoloader->getPaths()));
    }
    
    
    /**
     * @param String $class
     * @throws AutoloaderException_Index
     * @throws AutoloaderException_Index_NotFound
     * @return String The absolute path
     */
    public function getPath($class) {
    	try {
	    	$stmt = $this->getStatement(
	    	    "SELECT path FROM autoloadindex
	    	     WHERE class = ? AND context = ?"
	    	);
	    	$stmt->execute(array(
	    	    $class,
	    	    $this->getContext()
    	    ));
	    	$path = $stmt->fetchColumn();
	    	$stmt->closeCursor();
	    	if (! $path) {
	    		throw new AutoloaderException_Index_NotFound($class);
	    		
	    	}
	    	return $path;
	    	
    	} catch (PDOException $e) {
    		throw new AutoloaderException_Index($e->getMessage());
    		
    	} 
    }
    
    
    /**
     * @param String $class
     * @param String $path
     * @throws AutoloaderException_Index
     */
    protected function _setPath($class, $path) {
    	try {
    		$this->_unsetPath($class);
    		$this->getStatement(
    		    "INSERT INTO autoloadindex (path, class, context)
    		     VALUES (:path, :class, :context)"
    		)->execute(array(
    		    "class"   => $class,  
    		    "path"    => $path,
    		    "context" => $this->getContext()
    		));
    		
    	} catch (PDOException $e) {
            throw new AutoloaderException_Index($e->getMessage());
            
        }
    }
    
    
	/**
     * @param String $class
     * @throws AutoloaderException_Index
     */
    protected function _unsetPath($class) {
        try {
            $stmt = $this->getStatement(
                "DELETE FROM autoloadindex
                 WHERE class = ? AND context = ?"
            );
            $stmt->execute(array($class, $this->getContext()));
            
        } catch (PDOException $e) {
            throw new AutoloaderException_Index($e->getMessage());
            
        }
    }
    
    
    /**
     * @param String $class
     * @throws AutoloaderException_Index
     * @return bool
     */
    public function hasPath($class) {
        try {
            $stmt = $this->getStatement(
                "SELECT 1 FROM autoloadindex WHERE class = ? AND context = ?"
            );
            $stmt->execute(array($class, $this->getContext()));
            $hasPath = $stmt->fetchColumn();
            $stmt->closeCursor();
            
            return (bool) $hasPath;
            
        } catch (PDOException $e) {
            throw new AutoloaderException_Index($e->getMessage());
            
        } 
    }

    
    protected function save() {
        /**
         * @see _setPath()
         */
    }
    

}