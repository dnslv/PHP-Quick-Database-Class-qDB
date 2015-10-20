<?php

require_once("PDOWrapper.php");
require_once("QueryBuilder.php");

/**
 * qDB is an implementation class of PDOWrapper and QueryBuilder
 *
 * @author D. Nikolaev (https://twitter.com/d3nislav)
 */
class qDB
{
    /**
     * Implement singleton pattern to connect to DB through PDOWrapper
     *
     * @return QueryBuilder
     */
    private static function Instance()
    {

        static $qdb_instance = null;

        if (is_null($qdb_instance)) {

            $qdb_instance = new \QueryBuilder(
                new \PDOWrapper(
                    [
                        "host" => "127.0.0.1",
                        "dbname" => "r2",
                        "user" => "root",
                        "password" => ""
                    ]
                )
            );

        }

        return $qdb_instance;
    }

    /**
     *
     * A predefined functionality static method for easy execution
     *
     * @param $db_table_name
     * @return $this
     */
    public static function Table($db_table_name)
    {
        return static::Instance()->table($db_table_name);
    }


    public static function Query($query, $param_binds = [])
    {
        return static::Instance()->pdoInstance()->query($query,$param_binds);
    }
}
