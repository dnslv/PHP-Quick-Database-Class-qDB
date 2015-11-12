<?php

require_once "PDOWrapper.php";

class PDOWrapperTest extends PHPUnit_Framework_TestCase
{

    private $conn = false;

    private $dbc = array();

    public function SetUp()
    {
        $this->dbc = parse_ini_file("database.ini.php");

        if ($this->dbc) {

            $this->conn = new \PDOWrapper(
                    $this->dbc["driver"],
                    [
                        "host" => $this->dbc["host"],
                        "dbname" => $this->dbc["dbname"],
                        "user" => $this->dbc["user"],
                        "password" => $this->dbc["password"]
                    ]
                );
        }
    }

    public function dataBinds()
    {
        return array(

            ["param1", "value1"],
            ["param1", "value2"],
            ["param3", "value3"],
            ["1", 1],
            ["x", null]

            );
    }

    public function test_PDO_Connection()
    {
        $this->assertTrue($this->conn->Connect());
    }

    /**
     * @dataProvider dataBinds
     */
    public function test_parameter_binding($parameter, $value)
    {
        $this->conn->bind($parameter,$value);

        $this->assertTrue($this->conn->hasValue($value));

        $this->assertTrue($this->conn->hasParam($parameter));
    }
}
