<?php

/**
 * Class QueryBuilder
 *
 * @author D. Nikolaev (https://twitter.com/d3nislav)
 *
 *
 */
class QueryBuilder
{
    #Name of database table
    private $table = null;

    #Operation to perform (SELECT, DELETE, UPDATE, INSERT)
    private $action = null;

    #Where condition
    private $where = null;

    #Secondary conditions
    private $and_or_where = array();

    #Return results limit
    private $limit = null;

    #Set offset
    private $offset = null;

    #Arrange results by field
    private $order_by = null;

    #Arrange results by direction (ASC, DESC)
    private $order_by_dir = null;

    #Database fields to work with
    private $fields = null;

    #Bind parameters for insertion
    private $inserts = null;

    #Array with the current query binds
    private $bind = array();

    #Set to true via allowMultiple() method to be able to affect more than one record when UPDATING or DELETING
    private $allow_multiple = null;

    #an instance of class PDOWrapper
    private $pdo = null;

    /**
     * QueryBuilder class constructor
     *
     * @param PDOWrapper $pdo
     */
    function __construct(PDOWrapper $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Sets database table name
     *
     * @param $table_name
     * @return $this
     */
    public function table($table_name)
    {
        $this->table = $table_name;

        return $this;
    }

    /**
     * Alias of table() method
     *
     * @param $table_name
     * @return $this
     */
    public function from($table_name)
    {
        $this->table($table_name);

        return $this;
    }


    #BASIC CRUD OPERATIONS

    /**
     * Sets SELECT action. You can pass "*" for all or comma separated fields
     * the same way as you would do it in a SQL query
     *
     * @param string $select_what
     * @param bool|false $is_distinct
     * @return $this
     */
    public function select($select_what = "*", $is_distinct = false)
    {
        #Set query action
        if ($is_distinct) {

            $this->action = "SELECT DISTINCT";

        } else {

            $this->action = "SELECT";

        }

        #Check select_what
        if (is_string($select_what)) {

            $this->fields = $select_what;

        } else {

            throw new InvalidArgumentException('$select_what -> has to be string');

        }

        return $this;

    }

    /**
     * Sets INSERT action. As parameter accepts an array with keys corresponding to
     * database fields and values to be inserted in this fields
     *
     * @param $insert_array
     * @return $this
     */
    public function insert($insert_array)
    {
        $this->action = "INSERT";

        if (is_array($insert_array)) {

            foreach ($insert_array as $para => $val) {

                $this->fields .= $para . ", ";
                $this->inserts .= ":" . $para . ", ";
                $this->bind[$para] = $val;

            }
        }

        $this->fields = trim($this->fields);

        $this->fields = substr($this->fields, 0, strlen($this->fields) - 1);

        $this->inserts = trim($this->inserts);

        $this->inserts = substr($this->inserts, 0, strlen($this->inserts) - 1);

        return $this;

    }

    /**
     * Sets UPDATE action. You can pass a database field as string and set the second parameter
     * to value different than null, if you want to update single field in a table. If you pass an array
     * with keys corresponding to database fields and with values to be updated, and the second parameter
     * should not be set or set to null. Use this case if you have to update more than one field in a table.
     *
     * @param $update_what
     * @param null $value
     * @return $this
     */
    public function update($update_what, $value = null)
    {
        $this->action = "UPDATE";

        #SINGLE DATABASE FIELD UPDATE
        if (is_string($update_what) && !is_null($value)) {

            $param = $this->prepParam($update_what);

            $this->fields = "`" . $update_what . "`= :" . $param;

            $this->bind[$param] = $value;

        }

        #MULTIPLE DATABASE FIELDS UPDATE
        if (is_array($update_what) && is_null($value)) {

            $aggr_fields = "";

            foreach ($update_what as $update => $with_value) {

                $param = $this->prepParam($update);

                $aggr_fields .= "`" . $update . "`= :" . $param . ", ";

                $this->bind[$param] = $with_value;

            }

            $aggr_fields = trim($aggr_fields);

            $this->fields = substr($aggr_fields, 0, strlen($aggr_fields) - 1);

        }

        return $this;
    }

    /**
     * Sets DELETE action
     *
     * @return $this
     */
    public function delete()
    {
        $this->action = "DELETE";

        $this->fields = "";

        return $this;

    }


    #CONDITIONS

    /**
     * Sets a primary condition in the SQL query. $entity1 should be a field from a table. $operation is the
     * performed operation ("=", "<", ">", "LIKE"). $entity2 can be another table field, a comparison value, or
     * a SQL function. To use $entity2 as function, you have to set $entity2_is_function to true.
     *
     * @param $entity1
     * @param $operation
     * @param $entity2
     * @param bool|false $entity2_is_function
     * @return $this
     */
    public function where($entity1, $operation, $entity2, $entity2_is_function = false)
    {
        if ($entity2_is_function) {

            $this->where = "`" . $entity1 . "` " . $operation . " " . $entity2;

        } else {

            $param = $this->prepParam($entity1);

            $this->where = "`" . $entity1 . "` " . $operation . " :" . $param;

            $this->bind[$param] = $entity2;
        }

        return $this;

    }

    /**
     * Sets a secondary condition connected with AND to the primary or a preceding
     * secondary condition.
     *
     * SEE where() for more information.
     *
     * @param $entity1
     * @param $operation
     * @param $entity2
     * @param bool|false $entity2_is_function
     * @return $this
     */
    public function andWhere($entity1, $operation, $entity2, $entity2_is_function = false)
    {

        if ($entity2_is_function) {

            $this->and_or_where[] = "AND " . "`" . $entity1 . "` " . $operation . " " . $entity2;

        } else {

            $param = $this->prepParam($entity1);

            $this->and_or_where[] = "AND " . "`" . $entity1 . "` " . $operation . " :" . $param;

            $this->bind[$param] = $entity2;

        }

        return $this;
    }

    /**
     * Sets a secondary condition connected with OR to the primary or a preceding
     * secondary condition.
     *
     * SEE where() for more information.
     *
     * @param $entity1
     * @param $operation
     * @param $entity2
     * @param bool|false $entity2_is_function
     * @return $this
     */
    public function orWhere($entity1, $operation, $entity2, $entity2_is_function = false)
    {

        if ($entity2_is_function) {

            $this->and_or_where[] = "OR " . "`" . $entity1 . "` " . $operation . " " . $entity2;

        } else {

            $param = $this->prepParam($entity1);

            $this->and_or_where[] = "OR " . "`" . $entity1 . "` " . $operation . " :" . $param;

            $this->bind[$param] = $entity2;

        }

        return $this;
    }


    #RESULT FORMATTING

    /**
     * Limits the number of results that will be affected.
     *
     * @param $int_limit
     * @return $this
     */
    public function limit($int_limit)
    {
        if (is_integer($int_limit)) {

            $this->limit = (integer)$int_limit;

        }

        return $this;
    }

    /**
     * Sets offset value of affected results
     *
     * @param $int_offset
     * @return $this
     */
    public function offset($int_offset)
    {
        if (is_integer($int_offset)) {

            $this->offset = (integer)$int_offset;

        }

        return $this;
    }

    /**
     * Orders the results of a SELECT query by field
     *
     * @param $order_by_field
     * @return $this
     */
    public function orderBy($order_by_field)
    {
        $this->order_by = $order_by_field;

        return $this;
    }

    /**
     * Sets direction of orderBy()
     *
     * @param $direction
     * @return $this
     */
    public function orderByDirection($direction)
    {
        $options = ["ASC", "DESC"];

        if (in_array(strtoupper($direction), $options)) {

            $this->order_by_dir = strtoupper($direction);

        } else {

            throw new InvalidArgumentException('$direction - should be ASC or DESC');

        }

        return $this;

    }

    /**
     * Allows for multiple records to be affected if UPDATE or DELETE is executed.
     * By default, there is a limit of 1 record to be affected.
     *
     * @param bool|false $allow
     * @return $this
     */
    public function allowMultiple($allow = false)
    {
        $this->allow_multiple = $allow;

        return $this;
    }

    /**
     * Returns a prepared SQL query and an array of binds. Useful if using
     * a different PDO class from the provided(PDOWrapper)
     *
     * @return mixed
     */
    public function build()
    {
        $r["sql"] = $this->sqlQueryBuilder();
        $r["binds"] = $this->bind;
        $this->reset();

        return $r;
    }

    /**
     * Executes a built query and returns the results or false if unsuccessful.
     *
     * @return bool|mixed
     */
    public function run()
    {
        if ($sql = $this->sqlQueryBuilder()) {

            $r = $this->pdo->query($sql, $this->bind);

            $this->reset();

            return $r;

        } else {

            return false;

        }
    }

    public function pdoInstance()
    {
        return $this->pdo;
    }

    ###################
    # PRIVATE METHODS #
    ###################

    /**
     * Builds a sql query based on the set properties.
     * Returns prepared sql query on success or false if something is not set or is incorrect.
     *
     * @return bool|string
     */
    private function sqlQueryBuilder()
    {
        if (!is_null($this->table)) {

            $table = $this->table;

        } else {

            return false;

        }


        if (!is_null($this->fields)) {

            $fields = $this->fields;

        } else {

            return false;

        }

        $where = "";
        if (!is_null($this->where)) {

            $where = "WHERE " . $this->where;

        }

        $and_or_where = "";
        if (!is_null($this->and_or_where)) {

            if (!empty($this->and_or_where)) {

                foreach ($this->and_or_where as $condition) {

                    $and_or_where .= $condition . " ";

                }

                $and_or_where = trim($and_or_where);

            }

        }

        $order_by = "";
        if (!is_null($this->order_by)) {

            $order_by = $this->order_by;

        }

        $order_by_dir = "";
        if (!is_null($this->order_by_dir)) {

            $order_by_dir = $this->order_by_dir;

        }

        $limit = "";
        if (!is_null($this->limit) and is_integer($this->limit)) {

            $limit = "LIMIT " . $this->limit;

        }

        $offset = "";
        if (!is_null($this->offset) and is_integer($this->offset)) {

            $offset = "OFFSET " . $this->offset;

        }


        if (!is_null($this->action)) {

            if ($this->action === "SELECT") {

                $action = $this->action;

                return trim(sprintf("%s %s FROM %s %s %s %s %s %s %s", $action, $fields, $table, $where, $and_or_where, $order_by, $order_by_dir, $limit, $offset));

            } elseif ($this->action === "UPDATE") {

                $action = $this->action;

                if (empty($this->allow_multiple)) {

                    $limit = "LIMIT 1";

                }

                return trim(sprintf("%s %s SET %s %s %s %s", $action, $table, $fields, $where, $and_or_where, $limit));

            } elseif ($this->action === "DELETE" && !empty($where)) {

                $action = $this->action;

                if (empty($this->allow_multiple)) {

                    $limit = "LIMIT 1";

                }

                return trim(sprintf("%s FROM %s %s %s %s %s", $action, $table, $fields, $where, $and_or_where, $limit));

            } elseif ($this->action === "INSERT") {

                $action = $this->action;

                if (is_null($this->inserts)) {

                    return false;
                }

                return trim(sprintf("%s INTO %s(%s) VALUES(%s)", $action, $table, $fields, $this->inserts));


            }
        }

        return false;

    }


    /**
     * Resets all properties except the PDOWrapper class instance.
     */
    private function reset()
    {

        foreach ($this as $property => $value) {

            if (is_array($this->$property)) {

                $this->$property = array();

            } elseif (is_object($this->$property)) {

                continue;

            } else {

                $this->$property = null;

            }
        }

    }

    /**
     * Checks if parameter is duplicated in the bind array. If yes, creates an unique name.
     * @param $param
     * @return mixed
     */
    private function prepParam($param)
    {
        if (array_key_exists($param, $this->bind)) {

            $new_param = $param . rand(1, 9);

            return $this->prepParam($new_param);

        } else {

            return $param;

        }
    }
}
