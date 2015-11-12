# PHP Quick Database Class (qDB)

PHP Quick Database Class is fun and quick way to use PHP database.

PHP Quick Database Class consists of three classes:

- <b>PDOWrapper<b>
- <b>QueryBuilder<b>
- <b>qDB</b> (Implementation Class)

## What's new

- Supports Table Joining
- Database Settings are loaded from an ini file

## To-Do

- Unit testing

## Uses
- CRUD operations

## Basic Usage

	Include qDB.php in your project. If using autoloader, remove the class inclusions from qDB.php.
    
###HELPFUL:

To see the SQL query instead of run() method, use build(). Build() will return an array of PDO prepared statement and an array of all binds. Very usefull if something is not running right.
    
    
### EXAMPLES

##### SELECT
```php
    #SQL: SELECT * FROM Person
	$result = qDB::Table("Person")
	    ->select("*")
    	->run();

	#SQL: SELECT * FROM Person WHERE `name` = 'John'
    $result = qDB::Table("Person")
        ->select("*")
    	->where("name", "=", "John")
        ->run();

    #SQL: SELECT * FROM Person WHERE `name` LIKE '%ohn'
    $result = qDB::Table("Person")
        ->select("*")
        ->where("name", "LIKE", "%ohn")
        ->run();

    #SQL: SELECT * FROM Person WHERE `name` = 'John' AND `age` = 45
    $result = qDB::Table("Person")
    	->select("*")
    	->where("name", "=", "John")
        ->andWhere("age", "=", 45)
        ->run()

   #SQL: SELECT * FROM Person WHERE `membership` = 'user' OR `expires` < NOW()
    $result = qDB::Table("Person")
    	->select("*")
    	->where("membership", "=", "user")
        ->orWhere("expires", "<" , "NOW()", true)
        ->run();
```

##### INSERT
```php
    #SQL: INSERT INTO Person(`name`, `age`) VALUES('John',45)
	$result = qDB::Table("Person")
	    ->insert(["name"=>"John", "age"=>45])
	    ->run();
```

##### UPDATE
```php
    #SQL: UPDATE Person SET `name` = 'Nicole' WHERE `id` = 5
    $result = qDB::Table("Person")
        ->update("name", "Nicole")
    	->where("id", "=", 5)
        ->run();

    #SQL: UPDATE Person SET `name` = 'Nicole', `age` = '34' WHERE `id` = 10
    $result = qDB::Table("Person")
    	->update(["name" => "Nicole", "age" => 34])
    	->where("id", "=", 10)
        ->run();
```

##### DELETE
```php
    #SQL: DELETE FROM Persons WHERE `id` = 5
    $result = qDB::Table("Person")
    	->delete()
        ->where("id","=", 5)
        ->run();
 ```


##### JOINING
```php
    /*SQL 
    SELECT Orders.id AS order_id,Customers.name AS customer_name FROM Orders
    	LEFT JOIN Customers ON Orders.customer_id = Customers.id
    */
    $result = qDB::Table("Orders")
    	->select(["Orders.id AS order_id", "Customers.name AS customer_name"])
    	->join("Orders", "Customers", "customer_id", "id")
        ->run();
```

##### Use without QueryBuilder

```php
    $sql = "SELECT * FROM Person WHERE `name` = :name AND `age` = :age";
    $params = array("name"=>"John", "age"=>25);
    $result = qDB::Query($sql, $params);
 ```

##LICENSE
MIT LICENSE

