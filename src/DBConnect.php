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
            $this->dbh->query('SET NAMES utf8');
        } catch (\PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     * Closes cursor for next execution
     */
    private function closeCursor()
    {
        if ($this->stmt) {
            $this->stmt->closeCursor();
        }
    }

    /**
     * Executes an SQL statement
     * @param  string       $sql SQL statement
     * @return PDOStatement      Result set
     */
    public function query(string $sql)
    {
        $this->closeCursor();
        $this->stmt = $this->dbh->query($sql);
        return $this;
    }

    /**
     * Prevent cloning of the 'Singleton' instance
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserializing of the 'Singleton' instance
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}
