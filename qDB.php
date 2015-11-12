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

            $dbc = parse_ini_file("database.ini.php");

            if ($dbc) {

                $qdb_instance = new \QueryBuilder(
                    new \PDOWrapper(
                        $dbc["driver"],
                        [
                            "host" => $dbc["host"],
                            "dbname" => $dbc["dbname"],
                            "user" => $dbc["user"],
                            "password" => $dbc["password"]
                        ]
                    )
                );
            }


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

    /**
     *
     * Use to run your query directly
     *
     * @param $query
     * @param array $param_binds
     * @return mixed
     */
    public static function Query($query, $param_binds = [])
    {
        return static::Instance()->pdoInstance()->query($query,$param_binds);
    }
}

