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


InternalAutoloader::getInstance()->registerClass(
	'AutoloaderIndex',
    dirname(__FILE__).'/AutoloaderIndex.php'
);
InternalAutoloader::getInstance()->registerClass(
	'AutoloaderException_Index_NotFound',
    dirname(__FILE__).'/exception/AutoloaderException_Index_NotFound.php'
);


/**
 * The index is a PDO object.
 * 
 * This index uses a PDO object to store its data in any
 * database wich understands SQL. There is no need to
 * create any table. The index creates its structure by itself.
 * 
 * @see PDO 
 */
class AutoloaderIndex_PDO extends AutoloaderIndex {
    
	
	/**
	 * The name of the default SQLite database.
	 * 
	 * @see getSQLiteInstance()
	 */
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
    
    
    /**
     * Returns an index using a SQLite database.
     * 
     * If no filename is given a default database in the
     * temporary directory will be used.
     * 
     * @param String $filename
     * @return AutoloaderIndex_PDO
     * @see AutoloaderIndex_PDO::DEFAULT_SQLITE
     */
    static public function getSQLiteInstance($filename = null) {
    	if (is_null($filename)) {
    		$filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::DEFAULT_SQLITE;
    		
    	}
    	$pdo = new PDO("sqlite://$filename");
    	return new self($pdo);
    }
    
    
    /**
     * Initializes the index.
     * 
     * If the structure doesn't exist in the database it will be
     * created. The relation for the index is autoloadindex.
     */
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
     * @return PDO the PDO object of this index
     */
    public function getPDO() {
    	return $this->pdo;
    }
    
    
    /**
     * deletes the relation autoloadindex.
     * 
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
     * @param String $class
     * @throws AutoloaderException_Index
     * @throws AutoloaderException_Index_NotFound
     * @return String The absolute path
     */
    protected function _getPath($class) {
    	try {
	    	$stmt = $this->getStatement(
	    	    "SELECT path FROM autoloadindex
	    	     WHERE context = ? AND class = ?"
	    	);
	    	$stmt->execute(array(
	    	    $this->getContext(),    
	    	    $class
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
     * @throws AutoloaderException_Index
     * @return Array() All paths in the index
     */
    public function getPaths() {
        try {
            $stmt =  $this->getStatement(
	    	    "SELECT class, path FROM autoloadindex
	    	     WHERE context = ?"
	    	);
            $stmt->execute(array($this->getContext()));
            $paths = array();
            foreach ($stmt->fetchAll() as $data) {
                $paths[$data['class']] = $data['path'];

            }
            return $paths;

        } catch (PDOException $e) {
    		throw new AutoloaderException_Index($e->getMessage());

    	}
    }
    
    
    /**
     * @throws AutoloaderException_Index
     * @return int the size of the index
     */
    public function count() {
        try {
            $stmt = $this->getStatement(
                "SELECT count(*) FROM autoloadindex WHERE context = ?"
            );
            $stmt->execute( array($this->getContext()) );
            $count = $stmt->fetchColumn();
            $stmt->closeCursor();
            
            if ($count === false) {
                throw new AutoloaderException_Index("Couldn't SELECT count(*).");
                
            }
            return $count;
            
        } catch (PDOException $e) {
            throw new AutoloaderException_Index($e->getMessage());
            
        }
    }
    
    
    /**
     * Stores the path imediatly persistent.
     * 
     * There is no sense in making the class paths persistent
     * during {@link save()}. It is stored imediatly.
     * 
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
	 * Deletes the path imediatly persistent.
     * 
     * There is no sense in making the class paths persistent
     * during {@link save()}. It is deleted imediatly.
     * 
     * @param String $class
     * @throws AutoloaderException_Index
     */
    protected function _unsetPath($class) {
        try {
            $stmt = $this->getStatement(
                "DELETE FROM autoloadindex
                 WHERE context = ? AND class = ?"
            );
            $stmt->execute( array($this->getContext(), $class) );
            
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
                "SELECT 1 FROM autoloadindex WHERE context = ? AND class = ?"
            );
            $stmt->execute( array($this->getContext(), $class) );
            $hasPath = $stmt->fetchColumn();
            $stmt->closeCursor();
            
            return (bool) $hasPath;
            
        } catch (PDOException $e) {
            throw new AutoloaderException_Index($e->getMessage());
            
        } 
    }

    
    /**
     * Does nothing as {@link _setPath()} and {@link _unsetPath()} stored imediatly.
     * 
     * @see _setPath()
     * @see _unsetPath()
     */
    protected function _save() {
    }
    

}