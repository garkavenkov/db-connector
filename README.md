# db-connector
Wrapper for PDO

## Installation

Use [Composer](https://getcomposer.org "Composer")

>composer require garkavenkov/db-connector

## Usage

### Initiation

You can define database configuration parameters as **constants**

```php
<?php

require ('./vendor/autoload.php');

define('DB_USERNAME', 'username');
define('DB_PASSWORD', 'password');
define('DB_SCHEMA'  , 'schema_name');
define('DB_DRIVER'  , 'database_driver');
define('DB_HOSTNAME', 'hostame');

use DBConnector\DBConnect;

$dbh = DBConnect::getInstance();

```

or as an **associative array**.

```php
<?php

require ('./vendor/autoload.php');

$params = array(
    "db_username" => 'username',
    "db_password" => 'password',
    "db_schema"   => 'schema_name',
    "db_driver"   => 'database_driver',
    "db_hostname" => 'hostame'    
);

use DBConnector\DBConfig;
use DBConnector\DBConnect;

DBConfig::initiate($params);

$dbh = DBConnect::getInstance();
```

By default parameter `port` is empty. If you need to use specific port number you must define it:
```php
<?php

define('DB_PORT', 9999);
```
or, in case if configuration parameters are definid as an associative array ...
```php
<?php

$params = array(
    .
    .
    .
    "db_port" => 9999
);
```

## Methods

#### query(string $sql)
Executes an SQL statement and returns a result set as a  PDOStatement object

For example, this code set UTF8 code
```php
<?php

$sql  = "DROP TABLE IF EXISTS test; ";
$sql .= "CREATE TABLE test ( " ;
$sql .=   "id INT NOT NULL AUTO_INCREMENT, ";
$sql .=   "name VARCHAR(20) NOT NULL, ";
$sql .=   "PRIMARY KEY(id)";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=UTF8";

$dbh->query($sql);

```

#### execute(string $sql, array $params)
Prepares and executes SQL statement
```php
<?php
$sql  = "INSERT INTO `test` (`name`) ";
$sql .= "VALUES (:name)";

$param = [
    ':name' => 'John'
];

$dbh->execute($sql, $param);
```

#### getLastInsertedId()
Returns Id from last inserted record
```php
<?php

$id = $dbh->getLastInsertedId();
echo "Id: $id" . PHP_EOL;
```
Output:
```
Id: 1
```

#### getRow($fetch_method = null)
Fetches a record from result set. Result depends on `$fetch_method`. By default uses `PDO::FETCH_ASSOC`
```php
<?php
$sql = "SELECT * FROM `test`";
$row = $dbh->query($sql)->getRow();
var_dump($row);
```
Output:
```
array(2) {
  ["id"]=>
  string(1) "1"
  ["name"]=>
  string(4) "John"

}
```
For get result as an object use `PDO::FETCH_OBJ`
```php
<?php

$sql = "SELECT * FROM `test`";
$row = $dbh->query($sql)->getRow(PDO::FETCH_OBJ);
var_dump($row);
```

Output:
```
object(stdClass)#3 (2) {
  ["id"]=>
  string(1) "1"
  ["name"]=>
  string(4) "John"
}
```

#### getRows($fetch_method = null)
Fetches records from result set. Result depends on `$fetch_method`. By
default uses `PDO::FETCH_ASSOC`

```php
<?php

$sql  = "INSERT INTO `test` (`name`) ";
$sql .= "VALUES (:name)";

$param = [
    ':name' => 'Jane'
];

$dbh->execute($sql, $param);

$sql = "SELECT * FROM `test`";
$rows = $dbh->query($sql)->getRows();

var_dump($rows);
```
```
array(2) {
  [0]=>
  array(2) {
    ["id"]=>
    string(1) "1"
    ["name"]=>
    string(4) "John"
  }
  [1]=>
  array(2) {
    ["id"]=>
    string(1) "2"
    ["name"]=>
    string(4) "Jane"
  }
}
```

#### getFieldValue(string $field_name)
Returns value of particular field
```php
<?php

$sql = "SELECT * FROM `test` WHERE `id` = 1";
$name = $dbh->query($sql)->getFieldValue('name');

echo "Name: $name" . PHP_EOL;
```
Output:
```
Name: John
```
#### rowCount()
Returns the number of rows affected by the last SQL statement
```php
<? php

$sql = "DELETE FROM `test`";
$count = $dbh->query($sql)->rowCount();
echo "Count: $count" . PHP_EOL;
```
Output:
```
Count: 2
```
#### getAvailableDrivers()
Returns an array of available PDO drivers
```php
<?php

$drivers = $dbh->getAvailableDrivers();
print_r($drivers);
```
Output
```
Array
(
    [0] => mysql
    [1] => sqlite
)
```
