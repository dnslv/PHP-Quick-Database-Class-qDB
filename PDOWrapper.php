<?php

/**
 * PDOWrapper Class
 *
 * @author  D.Nikolaev (https://twitter.com/d3nislav)
 *
 * This class is simplified and modified version of PHP-MySQL-PDO-Database-Class by:
 *
 * @author        Author: Vivek Wicky Aswal. (https://twitter.com/#!/VivekWickyAswal)
 * @git        https://github.com/indieteq/PHP-MySQL-PDO-Database-Class
 * @version      0.2ab
 *
 */
class PDOWrapper
{
    # @object, The PDO object
    private $pdo;
    # @object, PDO statement object
    private $sQuery;
    # @array,  The database settings
    private $settings;
    # @bool ,  Connected to the database
    private $bConnected = false;
    # @array, The parameters of the SQL query
    private $parameters = array();
    # @bool,  The class is used in debug mode
    private $isDebugMode = true;
    #

    /**
     *   Default Constructor
     *
     *    1. Instantiate Log class.
     *    2. Connect to database.
     *    3. Creates the parameter array.
     * @param $settings
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->Connect();

    }

    /**
     *    This method makes connection to the database.
     *
     *    1. Reads the database settings from a ini file.
     *    2. Puts  the ini content into the settings array.
     *    3. Tries to connect to the database.
     *    4. If connection failed, exception is displayed and a log file gets created.
     */
    public function Connect()
    {
        $dsn = 'mysql:dbname=' . $this->settings["dbname"] . ';host=' . $this->settings["host"] . '';
        try {
            # Create instance of PDO; sets UTF8
            $this->pdo = new \PDO($dsn, $this->settings["user"], $this->settings["password"], array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

            # We can now log any exceptions on Fatal error.
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            # Disable emulation of prepared statements, use REAL prepared statements instead.
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

            # Connection succeeded, set the boolean to true.
            $this->bConnected = true;

            return true;

        } catch (\PDOException $e) {

            if ($this->isDebugMode) echo $e->getMessage();

            return false;
        }
    }

    /*
     *   You can use this little method if you want to close the PDO connection
     *
     */
    public function CloseConnection()
    {
        # Set the PDO object to null to close the connection
        # http://www.php.net/manual/en/pdo.connections.php
        $this->pdo = null;
    }

    /**
     *    Every method which needs to execute a SQL query uses this method.
     *
     *    1. If not connected, connect to the database.
     *    2. Prepare Query.
     *    3. Parameterize Query.
     *    4. Execute Query.
     *    5. On exception : Write Exception into the log + SQL query.
     *    6. Reset the Parameters.
     */
    private function Execute($query, $parameters = null)
    {
        # Connect to database
        if (!$this->bConnected) {

            if (!$this->Connect()) {

                return false;

            }
        }

        try {
            # Prepare query
            $this->sQuery = $this->pdo->prepare($query);

            # Add parameters to the parameter array
            $this->bindArray($parameters);

            # Bind parameters
            if (!empty($this->parameters)) {

                foreach ($this->parameters as $param => $value) {

                    switch (true) {
                        case is_int($value):
                            $type = \PDO::PARAM_INT;
                            break;
                        case is_bool($value):
                            $type = \PDO::PARAM_BOOL;
                            break;
                        case is_null($value):
                            $type = \PDO::PARAM_NULL;
                            break;
                        default:
                            $type = \PDO::PARAM_STR;
                    }


                    $this->sQuery->bindValue(":" . $param, $value, $type);
                }
            }
            # Execute SQL
            $x = $this->sQuery->execute();

        } catch (\PDOException $e) {

            if ($this->isDebugMode) echo $e->getMessage();

            return false;

        }
        # Reset the parameters
        $this->parameters = array();

        return true;
    }

    /**
     * @void
     *
     *    Add the parameter to the parameter array
     * @param string $para
     * @param string $value
     */
    public function bind($para, $value)
    {
        $nparam = $this->prepParam($para);
        $this->parameters[$nparam] = $value;
    }

    /**
     * @void
     *
     *    Add more parameters to the parameter array
     * @param array $parray
     */
    public function bindArray($parray)
    {
        if (is_array($parray)) {

            $columns = array_keys($parray);

            foreach ($columns as $column) {

                $this->bind($column, $parray[$column]);

            }
        }
    }

    /**
     *  If the SQL query  contains a SELECT or SHOW statement it returns an array containing all of the result set row
     *    If the SQL statement is a DELETE, INSERT, or UPDATE statement it returns the number of affected rows
     *
     * @param  string $query
     * @param  array $params
     * @param  int $fetchmode
     * @return mixed
     */
    public function query($query, $params = null, $fetchmode = \PDO::FETCH_ASSOC)
    {
        $query = trim($query);

        if (!$this->Execute($query, $params)) {

            return false;

        }

        $rawStatement = explode(" ", $query);

        # Which SQL statement is used
        $statement = strtolower($rawStatement[0]);

        if ($statement === 'select' || $statement === 'show') {

            return $this->sQuery->fetchAll($fetchmode);

        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {

            //return $this->sQuery->rowCount();

            return true;

        } else {

            return false;

        }
    }

    /**
     *  Returns the last inserted id.
     * @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Checks if parameter is duplicated in the bind array. If yes, creates an unique name.
     * @param $param
     * @return mixed
     */
    private function prepParam($param)
    {
        if (array_key_exists($param, $this->parameters)) {

            $new_param = $param . rand(1, 9);

            return $this->prepParam($new_param);

        } else {

            return $param;

        }
    }

    #UnitTest Helpers
    public function hasParam($param)
    {
        return array_key_exists($param, $this->parameters);
    }

    public function hasValue($value)
    {
        return in_array($value, $this->parameters);
    }
}
