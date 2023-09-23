# db-connector
**DBConnect** - Class-wrapper for PDO Class.   

## Installation

Use [Composer](https://getcomposer.org "Composer")

>composer require garkavenkov/db-connector

## Usage

### Initialization

There are two ways to set database configuration parameters. First one is through **constants**.

```php
<?php

require ('./vendor/autoload.php');

use DBConnector\DBConnect;

define('DB_USERNAME', 'username');
define('DB_PASSWORD', 'password');
define('DB_SCHEMA'  , 'schema_name');
define('DB_DRIVER'  , 'database_driver');
define('DB_HOSTNAME', 'hostname');

use DBConnector\DBConnect;

$dbh = DBConnect::getInstance();

```
Second one is through an **associative array** as a parameter in **getInstance()** method.

```php
<?php

require ('./vendor/autoload.php');

use DBConnector\DBConnect;

$params = array(
    "db_username" => 'username',
    "db_password" => 'password',
    "db_schema"   => 'schema_name',
    "db_driver"   => 'database_driver',
    "db_hostname" => 'hostname'    
);

$dbh = DBConnect::getInstance($params);
```

By default database parameter `db_port` is empty, thus database driver uses its own default port. If you need to use specific port number you must define it as a constant
```php
<?php

define('DB_PORT', 9999);
```
or as an associative array.
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

### exec(string $sql)
Executes an SQL statement and returns the number of affected rows
```php
<?php

// $dbh initialization

$sql  = "DROP TABLE IF EXISTS test; ";
$sql .= "CREATE TABLE test ( " ;
$sql .=   "id INT NOT NULL AUTO_INCREMENT, ";
$sql .=   "first_name VARCHAR(20) NOT NULL, ";
$sql .=   "last_name VARCHAR(25) NOT NULL, ";
$sql .=   "PRIMARY KEY(id)";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=UTF8";

$dbh->exec($sql);

$sql  = "INSERT INTO `test` (`first_name`, `last_name` ) VALUES ";
$sql .= "('John', 'Doe'),('Jane', 'Smith')";

$res = $dbh->exec($sql);
echo "Records were inserted: $res" . PHP_EOL;
```
Output:
```
Records were inserted: 2
```

### query(string $sql)
Executes an SQL statement and returns an DBConnect object with result set as a PDOStatement object. 

For example, this code set UTF8 code
```php
<?php

$sql = "SELECT * FROM `test`";
$dbh->query($sql);

var_dump($dbh);
```
Output:
```
object(DBConnector\DBConnect)#1 (2) {
  ["dbh":"DBConnector\DBConnect":private]=>
  object(PDO)#2 (0) {
  }
  ["stmt":"DBConnector\DBConnect":private]=>
  object(PDOStatement)#3 (1) {
    ["queryString"]=>
    string(20) "SELECT * FROM `test`"
  }
}
```

 After this method you can chain another method that works with PDOStatement object. For example `getRow()`

### getRow($fetch_style = null)
Fetches a row from a result set. The `$fetch_style` determines how PDO returns the row. By default uses `PDO::FETCH_ASSOC`
```php
<?php

$sql = "SELECT * FROM `test`";
$row = $dbh->query($sql)->getRow();
var_dump($row);
echo "Character: " . $person['first_name'] . " " . $person['last_name'] . PHP_EOL;
```
Output:
```
array(3) {
  ["id"]=>
  string(1) "1"
  ["first_name"]=>
  string(4) "John"
  ["last_name"]=>
  string(4) "Doe"
}
Character: John Doe
```
For getting result as an *object* use `PDO::FETCH_OBJ`
```php
<?php

$sql = "SELECT * FROM `test`";
$row = $dbh->query($sql)->getRow(PDO::FETCH_OBJ);
var_dump($row);
echo "Character: " . $person->first_name . " " . $person->last_name . PHP_EOL;
```
Output:
```
object(stdClass)#4 (3) {
  ["id"]=>
  string(1) "1"
  ["first_name"]=>
  string(4) "John"
  ["last_name"]=>
  string(4) "Doe"
}
Character: John Doe
```

### getRows($fetch_style = null)
Returns an array containing all of the result set rows. Result depends on `$fetch_style`. By
default uses `PDO::FETCH_ASSOC`

```php
<?php

$sql = "SELECT * FROM `test`";
$persons = $dbh->query($sql)->getRows();

foreach($persons as $person) {
    echo "Character: " . $person['first_name'] . " " . $person['last_name'] . PHP_EOL;
}
```

```
Character: John Doe
Character: Jane Smith
```

### prepare(string $sql, $standalone = false)
 Prepares an SQL statement for execution and returns DBConnect object that contains prepared PDOstatement.
```php
<?php
$sql  = "INSERT INTO `test` (`first_name`, `last_name`) ";
$sql .= "VALUES (:first_name, :last_name)";

$stmt = $dbh->prepare($sql);
var_dump($stmt)
```
Output:
```
object(DBConnector\DBConnect)#1 (2) {
  ["dbh":"DBConnector\DBConnect":private]=>
  object(PDO)#2 (0) {
  }
  ["stmt":"DBConnector\DBConnect":private]=>
  object(PDOStatement)#3 (1) {
    ["queryString"]=>
    string(79) "INSERT INTO `test` (`first_name`, `last_name`) VALUES (:first_name, :last_name)"
  }
}
```
If you set `$standalone=true`, this method returns a **PDOstatement** object rather than **DBConnect** object.
```php
$sql  = "INSERT INTO `test` (`first_name`, `last_name`) ";
$sql .= "VALUES (:first_name, :last_name)";

$stmt = $dbh->prepare($sql, true);
var_dump($stmt)
```
Output
```
object(PDOStatement)#3 (1) {
  ["queryString"]=>
  string(79) "INSERT INTO `test` (`first_name`, `last_name`) VALUES (:first_name, :last_name)"
}
```
### execute(array $params, $stmt = null)
Executes a prepared statement.
```php
<?php
$sql  = "INSERT INTO `test` (`fist_name`, `last_name`) ";
$sql .= "VALUES (:first_name, :lst_name)";

$param = [
    ':first_name' => 'Fhil',
    ':last_name'  => 'Johnson'
];

$dbh->prepare($sql)->execute($param);
```
If you set `$stmt` parameter this method will execute **standalone** prepared statement.

```php
<?php

```

### getLastInsertedId()
Returns Id from last inserted record
```php
<?php
$sql  = "INSERT INTO `test` (`fist_name`, `last_name`) ";
$sql .= "VALUES (:first_name, :last_name)";

$param = [
    ':first_name' => 'Fhil',
    ':last_name'  => 'Johnson'
];

$dbh->prepare($sql)->execute($param);

$id = $dbh->getLastInsertedId();
echo "Id: $id" . PHP_EOL;
```
Output:
```
Id: 3
```

#### getFieldValue(string $field_name)
Returns value of particular field
```php
<?php

$sql = "SELECT * FROM `test` WHERE `id` = 1";
$name = $dbh->query($sql)->getFieldValue('first_name');

echo "Name: $name" . PHP_EOL;
```
Output:
```
Name: John
```
### getFieldValues(string $field_name)
Returns an array that contains values from given field

```php
<?php

$sql = "SELECT * FROM `test`";

$names = $dbh->query($sql)->getFieldValues('first_name');

print_r($names); 

```
Output:

```
Array
(
    [0] => John
    [1] => Jane
    [2] => Fhil
)
```

### rowCount()
Returns the number of rows affected by the last SQL statement.
```php
<? php

$sql = "SELECT *  FROM `test`";
$count = $dbh->query($sql)->rowCount();
echo "Count: $count" . PHP_EOL;
```
Output:
```
Count: 3
```

### closeCursor()
Closes cursor for next execution.


### getAvailableDrivers()
Returns an array of available PDO drivers.
```php
<?php

$drivers = $dbh->getAvailableDrivers();
print_r($drivers);
```
Output:
```
Array
(
    [0] => mysql
    [1] => sqlite
)
```
