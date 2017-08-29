<?php

namespace DBConnector;

use DBConnector\DBConfig;

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
    public static function getInstance()
    {
        if (!(self::$conn instanceof self)) {
            self::$conn = new self();
        }
        return self::$conn;
    }

    /**
     * Creates a PDO connection
     */
    private function __construct()
    {
        // Database driver
        if (defined('DB_DRIVER')) {
            $db_driver = DB_DRIVER;
        } else {
            $db_driver = DBConfig::get('db_driver');
        }

        // Database hostname
        if (defined('DB_HOSTNAME')) {
            $db_hostname = DB_HOSTNAME;
        } else {
            $db_hostname = DBConfig::get('db_hostname');
        }

        // Database port
        if (defined('DB_PORT')) {
            $db_port = DB_PORT;
        } else {
            $db_port = DBConfig::get('db_port');
        }

        // Database schema name
        if (defined('DB_SCHEMA')) {
            $db_schema = DB_SCHEMA;
        } else {
            $db_schema = DBConfig::get('db_schema');
        }

        // Database user name
        if (defined('DB_USERNAME')) {
            $db_username = DB_USERNAME;
        } else {
            $db_username = DBConfig::get('db_username');
        }

        // Database user password
        if (defined('DB_PASSWORD')) {
            $db_password = DB_PASSWORD;
        } else {
            $db_password = DBConfig::get('db_password');
        }

        // DNS
        $dns = DBConfig::getDNS($db_hostname, $db_driver, $db_port, $db_schema);

        // Connect to the database
        try {
            $this->dbh = new \PDO($dns, $db_username, $db_password);
            $this->dbh->setAttribute(
                \PDO::ATTR_ERRMODE,
                \PDO::ERRMODE_EXCEPTION
            );
        } catch (\PDOException $e) {            
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Closes cursor for next execution
     */
    private function closeCursor()
    {
        echo "Close cursor." . PHP_EOL;
        var_dump($this->stmt);
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
        // $this->closeCursor();
        try {            
            $this->stmt = $this->dbh->query($sql);
            return $this;
        } catch (\PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Returns a row from a result set
     *
     * @param  int   $fetch_style   PDO fetch style
     * @return mixed                Record from result set depending on PDO fetch style
     */
    public function getRow($fetch_style = null)
    {
        if (is_null($fetch_style)) {
            $fetch_method = \PDO::FETCH_ASSOC;
        }
        try {
            return $this->stmt->fetch($fetch_style);
        } catch (\PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    /**
     * Returns rows from a result set
     *
     * @param  int    $fetch_style    PDO fetch style
     * @return mixed  Records from result set depending on PDO fetch style
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
     * @param  string $sql     SQL statement     
     * @return self
     */
    public function prepare(string $sql, $alone = false)
    {
        // Close previous PDO statement
        // $this->closeCursor();
        try {
            $stmt = $this->dbh->prepare($sql);            
            // $this->dbh->prepare($sql);
            if ($alone) {
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
     * Execute an SQL statement and return the number of affected rows 
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
        } catch (\PDException $e) {
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
    private function __wakeup()
    {
    }
}
