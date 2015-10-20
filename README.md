# PHP QuickDB (qDB)

PHP Quick DB is fun and quick way to use PHP database.

PHP Quick DB consists of three classes:

- <b>PDOWrapper<b>
- <b>QueryBuilder<b>
- <b>qDB</b> (Implementation Class)

## Uses
- CRUD operations

*!!! DOES NOT SUPPORT TABLE JOINING WHEN USING QUERYBUILDER !!!*

## Basic Usage

	Include qDB.php in your project. If using autoloader, remove the class inclusions from qDB.php.
    
### EXAMPLES

##### SELECT
```php
    #SQL: SELECT * FROM Person
	$result = qDB::Table("Person")->select("*")->run();

	#SQL: SELECT * FROM Person WHERE `name` = 'John'
    $result = qDB::Table("Person")->select("*")->where("name", "=", "John")->run();

    #SQL: SELECT * FROM Person WHERE `name` LIKE '%ohn'
    $result = qDB::Table("Person")->select("*")->where("name", "LIKE", "%ohn")->run();

    #SQL: SELECT * FROM Person WHERE `name` = 'John' AND `age` = 45
    $result = qDB::Table("Person")->select("*")->where("name", "=", "John")->andWhere("age", "=", 45)->run()

   #SQL: SELECT * FROM Person WHERE `membership` = 'user' OR `expires` < NOW()
    $result = qDB::Table("Person")->select("*")
    							  ->where("membership", "=", "user")
                                  ->orWhere("expires", "<" , "NOW()", true)
                                  ->run();
```

##### INSERT
```php
    #SQL: INSERT INTO Person(`name`, `age`) VALUES('John',45)
	$result = qDB::Table("Person")->insert(["name"=>"John", "age"=>45])->run();
```

##### UPDATE
```php
    #SQL: UPDATE Person SET `name` = 'Nicole' WHERE `id` = 5
    $result = qDB::Table("Person")->update("name", "Nicole")->where("id", "=", 5)->run();

    #SQL: UPDATE Person SET `name` = 'Nicole', `age` = '34' WHERE `id` = 10
    $result = qDB::Table("Person")->update(["name" => "Nicole", "age" => 34])->where("id", "=", 10)->run();
```

##### DELETE
```php
    #SQL: DELETE FROM Persons WHERE `id` = 5
    $result = qDB::Table("Person")->delete()->where("id","=", 5)->run();
 ```

 #### Use without QueryBuilder

```php
    $sql = "SELECT * FROM Person WHERE `name` = :name AND `age` = :age";
    $params = array("name"=>"John", "age"=>25);
    $result = qDB::Query($sql, $params);
 ```

##LICENSE
MIT LICENSE

