<?php

require_once "QueryBuilder.php";
require_once "PDOWrapper.php";

class QueryBuilderTest extends PHPUnit_Framework_TestCase
{
    protected $qb;

    protected function SetUp()
    {
        $this->qb = new QueryBuilder(new PDOWrapper());
    }



    public function test_table_set($table_name)
    {

    }

    public function test_select_action_is_set($_name)
    {


    }

    public function test_insert_action_is_set($table_name)
    {


    }

    public function test_update_action_is_set($table_name)
    {


    }

    public function test_delete_action_is_set($table_name)
    {


    }

}
