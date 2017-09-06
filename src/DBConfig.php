<?php

namespace DBConnector;

/**
 * Class: DBConfig
 *
 * Works with database configuration parametes.
 *
 * @package DBConnector
 * @author  Maksim Garkavenkov <maksim.garkavenkov@gmail.com>
 */
class DBConfig
{
    /**
     * Database configuration parameters
     * @var array
     */
    private static $db_params = [];

    /**
     * Initiates database configuration parameters
     * @param  array  $params Database configuration parameters
     * @return void
     */
    public static function initiate(array $params)
    {
        try {
            //  Check whether $params is an array or not
            if (!is_array($params)) {
                throw new \Exception('Parameters need to be as an associative array');
            }

            // Initiate database hostname.
            if (isset($params['db_hostname'])) {
                self::$db_params['db_hostname'] = $params['db_hostname'];
            } else {
                throw new \Exception('Database host need to be set!');
            }

            // Initiate  database driver.
            if (isset($params['db_driver'])) {
                self::$db_params['db_driver'] = $params['db_driver'];
            } else {
                throw new \Exception('Database driver need to be set!');
            }

            // Initiate database port
            if (isset($params['db_port'])) {
                self::$db_params['db_port'] = ":" . $params['db_port'];
            } else {
                self::$db_params['db_port'] = "";
            }

            // Initiate database schema
            if (isset($params['db_schema'])) {
                self::$db_params['db_schema']= $params['db_schema'];
            } else {
                throw new \Exception('Database name need to be set!');
            }

            // Initiate database username
            if (isset($params['db_username'])) {
                self::$db_params['db_username'] = $params['db_username'];
            } else {
                self::$db_params['db_username'] = "";
            }

            // Initiate database password
            if (isset($params['db_password'])) {
                self::$db_params['db_password'] = $params['db_password'];
            } else {
                self::$db_params['db_password'] = "";
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Makes DSN
     * @param  string $db_hostname Database hostname
     * @param  string $db_driver   Database driver name
     * @param  int    $db_port     Database port
     * @param  string $db_schema   Database name
     * @return string              DSN
     */
    public static function getDSN($db_hostname = null, $db_driver = null, $db_port = null, $db_schema = null): string
    {
        try {
            // Check whether $db_hostname is set
            if (!$db_hostname) {
                // if not, try to use self::$db_params['db_hostname']
                if (!isset(self::$db_params['db_hostname'])) {
                    throw new \Exception('Database host not set');
                } else {
                    $db_hostname = self::$db_params['db_hostname'];
                }
            }

            // Check whether $db_driver is set
            if (!$db_driver) {
                // if not, try to use self::$db_params['db_driver']
                if (!isset(self::$db_params['db_driver'])) {
                    throw new \Exception('Database driver not set');
                } else {
                    $db_driver = self::$db_params['db_driver'];
                }
            }

            // Check whether $db_schema is set
            if (!$db_schema) {
                // if not, try to use self::$db_params['db_schema']
                if (!isset(self::$db_params['db_schema'])) {
                    throw new \Exception('Database name not set');
                } else {
                    $db_schema = self::$db_params['db_schema'];
                }
            }

            // Check whether $db_port is set
            if (!$db_port) {
                // if not, try to use self::$db_params['db_port']
                if (!isset(self::$db_params['db_port'])) {
                    // throw new \Exception('Database port not set');
                    $db_port = "";
                } else {
                    $db_port = self::$db_params['db_port'];
                }
            }

            // Make DNS
            if ($db_driver === 'mysql') {
                $dsn =  $db_driver .
                        ":host=" .
                        $db_hostname;

                $db_port = empty($db_port) ? "" : (":" . $db_port) ;

                $dsn .= $db_port .
                        ";dbname=" .
                        $db_schema;
            } elseif ($db_driver === 'sqlite') {
                // In case database type is sqlite,
                // 'dbname' param stores path to the database file.
                $dsn =  $db_driver . ':'. $db_schema;
            } else {
                throw new \Exception("Database type '". self::$db_params['db_type'] . "' does not support yet.");
            }

            return $dsn;
        } catch (\Exception $e) {
            die('Error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Returns parameter's value if exists, otherwise returns null
     * @param  string $param Parameter's name
     * @return mixed         Parameter's value
     */
    public static function get($param)
    {
        if (isset(self::$db_params[$param])) {
            return self::$db_params[$param];
        }
        return false;
    }
}
