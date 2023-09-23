<?php

namespace DBConnector;

/**
 * Class: DBConnect
 *
 * Wrapper for PDO connection to the database
 *
 * @package DBConnector
 * @author  Maksim Garkavenkov <maksim.garkavenkov@gmail.com>
 */
class DBConnect
{
    /**
     * Database's ports by default
     *
     * @var array
     */
    private static $default_ports = array(
        'mysql' =>  3306
    );

    /**
     * Database handler
     *
     * @var PDO instance
     */
    private $dbh;

    /**
     * The singletone instance
     *
     * @var DBConnect instance
     */
    private static $conn;

    /**
     * Result set
     *
     * @var PDOStatement;
     */
    private $stmt;

    /**
     * Makes an instance available as a singleton
     *
     * @return self     Singleton instance
     */
    public static function getInstance($params = [])
    {
        if (!(self::$conn instanceof self)) {
            self::$conn = new self($params);
        }
        return self::$conn;
    }

    /**
     * Makes DSN
     * @param  string $db_hostname Database hostname
     * @param  string $db_driver   Database driver name
     * @param  int    $db_port     Database port
     * @param  string $db_schema   Database name
     * @return string              Data Source Name
     */
    private static function getDSN($db_hostname = null, $db_driver = null, $db_port = null, $db_schema = null): string    
    {
        try {            
            // Make DSN
            if ($db_driver === 'mysql') {
                if (is_null($db_hostname)) {
                    throw new \Exception('Database host not set');                    
                }                
                $dsn =  $db_driver .
                        ":host=" .
                        $db_hostname;

                $db_port = empty($db_port) ? "" : (":" . $db_port) ;

                $dsn .= $db_port .
                        ";dbname=" .
                        $db_schema;
            } elseif ($db_driver === 'sqlite') {
                // In case database type is sqlite,
                // 'db_name' param stores path to the database file.
                $dsn =  $db_driver . ':'. $db_schema;
            } else {
                throw new \Exception("Database type '". $db_driver . "' does not support yet.");
            }            
            return $dsn;
        } catch (\Exception $e) {
            die('Error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Creates a PDO connection
     */
    private function __construct($params = [])
    {   
        try {

            // Database driver
            if (defined('DB_DRIVER')) {
                $db_driver = DB_DRIVER;
            } elseif (isset($params['DB_DRIVER'])) {
                $db_driver = $params['DB_DRIVER'];
            } else {
                throw new \Exception('Database driver not set');
            }                
            
            // Database hostname
            if (defined('DB_HOSTNAME')) {
                $db_hostname = DB_HOSTNAME;
            } elseif (isset($params['DB_HOSTNAME'])) {
                $db_hostname = $params['DB_HOSTNAME'];
            } else {
                $db_hostname = null;                
            }             
            
            // Database port
            if (defined('DB_PORT')) {
                $db_port = DB_PORT;
            } elseif (isset($params['DB_PORT'])) {
                $db_port = $params['DB_PORT'];
            } else {
                $db_port = isset(self::$default_ports[$db_driver]) ? self::$default_ports[$db_driver] : null;
            }                
            
            // Database schema name
            if (defined('DB_SCHEMA')) {
                $db_schema = DB_SCHEMA;
            } elseif (isset($params['DB_SCHEMA'])) {
                $db_schema = $params['DB_SCHEMA'];
            } else {
                throw new \Exception('Database name need to be set!');
            }

            // Database user name
            if (defined('DB_USERNAME')) {
                $db_username = DB_USERNAME;
            } elseif (isset($params['DB_USERNAME'])) {
                $db_username = $params['DB_USERNAME'];
            } else {
                $db_username = null;
            }            
            
            // Database user password
            if (defined('DB_PASSWORD')) {
                $db_password = DB_PASSWORD;
            } elseif (isset($params['DB_PASSWORD']))  {
                $db_password = $params['DB_PASSWORD'];
            } else {
                $db_password = null;                
            }
            
            $dsn = self::getDSN($db_hostname, $db_driver, $db_port, $db_schema);                        
            $this->dbh = new \PDO($dsn, $db_username, $db_password);
            $this->dbh->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
        } catch(\PDOException $e) {
            die('Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            die("Error: " . $e->getMessage());            
        }
    }

    /**
     * Closes cursor for next execution
     */
    private function closeCursor()
    {
        try {
            if ($this->stmt) {
                $this->stmt->closeCursor();
            }
        } catch (\PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Executes an SQL statement
     *
     * @param  string       $sql SQL statement
     * @return $this
     */
    public function query(string $sql)
    {
        try {
            $this->stmt = $this->dbh->query($sql);            
            return $this;
        } catch (\PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Returns a next row from a result set
     *
     * @param  int   $fetch_style   PDO fetch style
     * @return mixed                Record from result set depending on PDO fetch style
     */
    public function getRow($fetch_style = null)
    {
        if (is_null($fetch_style)) {
            $fetch_style = \PDO::FETCH_ASSOC;
        }
        try {
            return $this->stmt->fetch($fetch_style);
        } catch (\PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Returns an array containing all of the result set rows
     *
     * @param  int    $fetch_style    PDO fetch style
     * @return array  Records from result set depending on PDO fetch style
     */
    public function getRows($fetch_style = null)
    {        
        if (is_null($fetch_style)) {
            $fetch_style = \PDO::FETCH_ASSOC;
        }
        try {
            return $this->stmt->fetchAll($fetch_style);
        } catch (\PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Prepares an SQL statement
     *
     * @param  string  $sql         SQL statement
     * @param  boolean $standalone  
     * @return self
     */
    public function prepare(string $sql, $standalone = false)
    {
        try {
            $stmt = $this->dbh->prepare($sql);
            if ($standalone) {
                return $stmt;
            } else {
                $this->stmt = $stmt;
            }
            return $this;
        } catch (\PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Executes an SQL statement
     *
     * @param  array  $params  Parameters as an associative array
     * @param  PDO statememt  $stmt  Prepared statement
     * @return self
     */
    public function execute(array $params, $stmt = null)
    {
        try {
            if (!is_null($stmt)) {
                $this->stmt = $stmt->execute($params);
            } else {
                if (!$this->stmt) {
                    throw new \Exception("Prepared stetement not found.");
                }
                $this->stmt->execute($params);
            }
            return $this;
        } catch (\PDOException $e) {
            die("Error: " . $e->getMessage());
        } catch (\Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Executes an SQL statement and returns the number of affected rows
     *
     * @param string $sql SQL satement
     * @return int        Number of rows
     */
    public function exec(string $sql)
    {
        try {
            return $this->dbh->exec($sql);
        } catch (\PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Returns field's value
     *
     * @param  string $field_name Field's name
     * @return mixed              Field's value
     */
    public function getFieldValue(string $field_name)
    {
        try {
            $value = null;
            $fields = $this->stmt->fetch(\PDO::FETCH_ASSOC);
            if (array_key_exists($field_name, $fields)) {
                $value = $fields[$field_name];
            } else {
                throw new \Exception("Field '$field_name' is not present in the current result set.\n");
            }
            return $value;
        } catch (\PDOException $e) {
            die('Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Returns field's values
     *
     * @param string $field_name    Field's name
     * @return array                Array with field's values
     */
    public function getFieldValues(string $field_name)
    {
        try {
            $field = [];
            $result = $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (array_key_exists($field_name, $result[0])) {
                foreach ($result as $row) {
                    $field[] = $row[$field_name];
                }
            } else {
                throw new \Exception("Field '$field_name' is not present in the current result set.\n");
            }
            return $field;
        } catch (\PDOException $e) {
            die('Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Returns the number of rows affected by the last SQL statement
     *
     * @return int  Number of rows
     */
    public function rowCount()
    {
        try {
            if ($this->stmt) {
                return $this->stmt->rowCount();
            }
            return null;
        } catch (\PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Returns Id from last inserted record
     *
     * @return int  Id
     */
    public function getLastInsertedId()
    {
        try {
            return $this->dbh->lastInsertId();
        } catch (\PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Returns an array of available PDO drivers
     *
     * @return array    Available PDO drivers
     */
    public function getAvailableDrivers()
    {
        try {
            return $this->dbh->getAvailableDrivers();
        } catch (\PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Prevents cloning of the 'Singleton' instance
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Prevents unserializing of the 'Singleton' instance
     *
     * @return void
     */
    public function __wakeup()
    {
    }
}
