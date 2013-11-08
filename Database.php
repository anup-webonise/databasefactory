<?php
/**
 * Created by Anup Kale.
 * User: Anup Kale
 * Date: 7/11/13
 * Time: 4:31 PM
 * To change this template use File | Settings | File Templates.
 */

require_once('DatabaseFactory.php');
require_once('Query.php');

class Database
{
    /**
     * @db = this is PDO object
     */
    private $db = null;

    /**
     * @query = this variable will be used to store the query
     */
    private $query = null;

    /**
     * @var null
     */
    private $where = null;

    /**
     * @var null
     */
    private $update = null;

    /**
     * @var null
     */
    private $groupBy = null;

    /**
     * @var null
     */
    private $orderBy = null;

    /**
     * @var null
     */
    private $select = null;

    /**
     * @var null
     */
    private $join = null;

    /**
     * @var null
     */
    private $from = null;

    /**
     * @statement = this is the statement that will be executed
     */
    private $statement = null;

    /**
     * @prepareStatementArray = this is the statement that will be executed
     */
    private $prepareStatementArray = null;

    /**
     * @isWhereFunctionCalled = This variable will be used to track whether where function has been called
     * before calling update delete functions. This will avoid table level updates and deletes
     * 0 => Not Called
     * 1 => Called
     */
    private $isWhereFunctionCalled = 0;

    /*
     * @constructor set custom exception handler for the object.
     * create a singleton PDO object from the final class Database Factory
     */
    function __construct()
    {
        // Temporarily change the PHP exception handler while we . . .
        set_exception_handler(array(__CLASS__, 'throw_exception'));

        $this->db = DatabaseFactory::getInstance();
    }

    /*
     * @destructor unset custom exception handler for the object.
     * destroy the PDO object of the class Database
     */
    function __destruct()
    {
        unset($this->db);
        unset($this->statement);
        unset($this->query);
        restore_exception_handler();
    }

    /**
     * @param Exception $exception
     */
    public function throw_exception(Exception $exception)
    {
        echo "<pre>";
        print_r($exception);
        die();
        echo($exception->getMessage());
    }

    /**
     * @param null $tableName
     *
     * @return true or false
     *
     * @throws Exception = Table name has to be present
     */
    private function check_table_name_is_present($tableName = NULL)
    {
        if(empty($tableName))
        {
            throw new Exception("Table name cannot be empty in an insert statement");
        }
        return true;
    }

    /**
     * @param null $field
     *
     * @return true or false
     *
     * @throws Exception = Field has to be present
     */
    private function check_if_field_is_present($field = NULL)
    {
        if(empty($field))
        {
            throw new Exception("Field has to be present");
        }
        return true;
    }

    /**
     * @param array $fields
     *
     * @return true or false
     *
     * @throws Exception Fields have to be present.
     */
    private function check_if_fields_are_correct($fields = array())
    {
        if(empty($fields))
        {
            throw new Exception("Fields have to be present in an insert statement");
        }

        foreach($fields as $key => $value)
        {
            if(is_array($value))
            {
                throw new Exception("I don't know what to do with multidimensional arrays");
            }
        }

        return true;
    }

    /**
     * @param $bindColumnArray These are the fields that have to be bound in the
     * query that is prepared by the prepare function of PDO
     */
    private function bind_values($bindColumnArray)
    {
        foreach($bindColumnArray as $key => $value)
        {
            $this->statement->bindValue($key, $value);
        }
    }

    /**
     * Execute function executes the query.
     */
    private function execute_statement()
    {
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->statement->execute();
        $this->wrap_up_query();
        return;
    }

    /**
     * @return string = this is the query that will be sent to the query() function
     */
    private function query_builder()
    {
        if(!empty($this->update))
        {
            $query = $this->update.' '.$this->join.' '.$this->where;
        }
        else
        {
            $query = $this->select.' '.$this->from.' '.$this->join.' '.$this->where.' '.
                $this->orderBy.' '.$this->groupBy;
        }

        return $query;
    }

    private function wrap_up_query()
    {
        $this->query = null;

        $this->where = null;

        $this->update = null;

        $this->groupBy = null;

        $this->orderBy = null;

        $this->select = null;

        $this->join = null;

        $this->from = null;

        $this->prepareStatementArray = null;
    }

    private function prepare_statement($query)
    {
        if(empty($query))
        {
            throw new Exception("Query cannot be empty");
        }
        /**
         * Prepare the PDO query.
         */

        $this->statement = $this->db->prepare($query);

        /**
         * Check if the prepare statement array is present
         * If the array is present then bind the values
         */

        if(!empty($this->prepareStatementArray))
        {
            $this->bind_values($this->prepareStatementArray);
        }

    }

    /**
     * @param $query = This is query provided to be executed
     *
     * @public function query = this function has been made public so that a user can create a
     *  custom query that can be directly executed.
     *
     * @throws Exception = Query has to be present.
     */
    public function query($query)
    {
       /* echo "<pre>";
        print_r($query);
        print_r($this->prepareStatementArray);
        die();*/
        /*
         * Check if query is not empty if the query is empty
         * throw a custom error message
         */
        if(empty($query))
        {
            throw new Exception("Query cannot be empty");
        }

        /**
         * Execute the query.
         */
        $this->execute_statement();
    }

    /**
     * @param null $tableName = table where the data has to be inserted
     *
     * @param array $fields = fields that will be inserted.
     */
    public function insert($tableName = NULL, $fields = array())
    {
        /*
         * Check if table name is not empty
         */

        $this->check_table_name_is_present($tableName);

        /*
         * Check if $fields is not empty
         */

        $this->check_if_fields_are_correct($fields);

        /**
         * Table name and fields are present and hence we can now go
         * ahead and create the Insert statement.
         */
        $this->query = "INSERT INTO `".$tableName."` (";

        /**
         * Create the field list for update and create the
         * bind array for the prepare statement for PDO
         */
        $count = 1;
        $noOfFields = count($fields);
        foreach($fields as $key => $value)
        {
            if($count < $noOfFields)
            {
                $this->query .= '`'.$key.'`, ';
            }
            else
            {
                $this->query .= '`'.$key.'`) ';
            }

            if($value === NULL)
                $this->prepareStatementArray[':field'.$count] = '';
            else
                $this->prepareStatementArray[':field'.$count] = $value;

            $count++;
        }

        $this->query .='VALUES(';

        /**
         * Create the values for the insert statement
         */
        $count = 1;
        foreach($fields as $key => $value)
        {
            if($count < $noOfFields)
            {
                $this->query .= ':field'.$count.', ';
            }
            else
            {
                $this->query .= ':field'.$count.')';
            }

            $count++;
        }

        /**
         * Pass the query and the prepared statement array to the query function.
         */
        $this->prepare_statement($this->query);
        $this->execute_statement();
    }

    /**
     * @param null $tableName = table where the data has to be inserted
     *
     * @param array $fields = fields that will be inserted.
     *
     * @throws Exception = if the Where() function is not called before the update()
     */
    public function update($tableName = NULL, $fields = array())
    {

        if($this->isWhereFunctionCalled == 0)
        {
            throw new Exception("Please create the where clause by calling where() before calling update()");
        }

        /*
         * Check if table name is not empty
         */

        $this->check_table_name_is_present($tableName);

        /*
         * Check if $fields is not empty
         */

        $this->check_if_fields_are_correct($fields);

        /**
         * Table name and fields are present and hence we can now go
         * ahead and create the Insert statement.
         */
        $query = "UPDATE `".$tableName."` SET ";

        /**
         * Create the field list for update and create the
         * bind array for the prepare statement for PDO
         */
        $count = 1;
        $noOfFields = count($fields);
        foreach($fields as $key => $value)
        {
            if($count < $noOfFields)
            {
                $query .= '`'.$key.'` = :'.$key.' , ';
            }
            else
            {
                $query .= '`'.$key.'` = :'.$key.'';
            }

            if($value === NULL)
                $this->prepareStatementArray[':'.$key] = '';
            else
                $this->prepareStatementArray[':'.$key] = $value;

            $count++;
        }
        /**
         * Pass the query and the prepared statement array to the query function.
         */
        $this->update = $query;

        $this->query = $this->query_builder();

        $this->prepare_statement($this->query);

        $this->execute_statement();
    }

    /**
     * @param $field
     * @param null $value
     * @return Database
     * @throws Exception
     */
    public function where($field , $value = NULL)
    {
        /**
         * Check if the field is present in the where clause
         */
        $this->check_if_field_is_present($field);

        if(is_array($value) or is_object($value))
        {
            throw new Exception('Values cannot be an array or an objcet');
        }

        /**
         * @fieldAndOperator = this will hold field and the operator
         */
        $fieldAndOperator = array();

        $fieldAndOperator = explode(' ', $field);

        if(count($fieldAndOperator) > 2 or empty($fieldAndOperator))
        {
            throw new Exception("There is an error in the field");
        }

        $query = 'WHERE '.$fieldAndOperator[0].' ';

        if(!empty($fieldAndOperator[1]))
        {
            $query .= $fieldAndOperator[1].' ';
        }
        else if(empty($fieldAndOperator[1]) and !empty($value))
        {
            $query .='= ';
        }

        if(!empty($value))
        {
            $query .= ':'.$value.' ';
        }

        $this->prepareStatementArray[':'.$value] = $value;

        $this->where = $query;

        $this->isWhereFunctionCalled = 1;

        return $this;
    }

    /**
     * @param $tableName
     * @param $condition
     * @param string $type
     * @return Database
     * @throws Exception
     */
    public function join($tableName, $condition, $type = 'LEFT')
    {
        /*
        * Check if table name is not empty
        */

        $this->check_table_name_is_present($tableName);

        if(isarray($condition) or is_object($condition) or empty($condition))
        {
            throw new Exception ('There is an error with join condition');
        }

        $this->join .= $type.' JOIN '.$tableName.' ON '.$condition;

        return $this;
    }

    public function group_by($fields)
    {
        /**
         * Check if fields are present
         */
        $this->check_if_field_is_present($fields);

        $this->groupBy .= 'GROUP BY '.$fields;

        return $this;
    }

    public function order_by($fields)
    {
        /**
         * Check if fields are present
         */
        $this->check_if_field_is_present($fields);

        $this->orderBy .= 'ORDER BY '.$fields;

        return $this;
    }

    public function select($fields, $classifier = true)
    {
        /**
         * Check if fields are present
         */
        $this->check_if_field_is_present($fields);

        $fieldsArray = explode(',', $fields);

        $this->select = "SELECT ";

        $count = 1;
        $fieldsCount = count($fieldsArray);

        foreach($fieldsArray as $field)
        {
            if(rtrim(ltrim($field)) != '*' and $classifier == true)
            {
                $this->select .='`'.rtrim(ltrim($field)).'` ';
            }
            else if(rtrim(ltrim($field)) != '*' and $classifier == false)
            {
                $this->select .=rtrim(ltrim($field)).' ';
            }
            else if(rtrim(ltrim($field)) == '*')
            {
                $this->select .= '* ';
            }

            if($count < $fieldsCount)
            {
                $this->select .=',';
            }
        }

        return $this;
    }

    public function from ($tableNames,$classifier = true)
    {
        /**
         * Check if fields are present
         */
        $this->check_if_field_is_present($tableNames);

        $tablesArray = explode(',', $tableNames);

        $this->from = "FROM (";

        $count = 1;
        $tableCount = count($tablesArray);

        foreach($tablesArray as $table)
        {
            if($classifier == true)
            {
                $this->from .='`'.rtrim(ltrim($table)).'` ';
            }
            else if($classifier == false)
            {
                $this->from .=rtrim(ltrim($table)).' ';
            }
        }

        $this->from .=') ';

        return $this;
    }
    
    public function get()
    {
        $this->query = $this->query_builder();
        $this->prepare_statement($this->query);
        $this->execute_statement();
    }
}


$db = new Database();

//$a->insert('test', array('field1' => null, 'field2' => 1009 , 'field3' => 2009));
$db->where('`field1` !=', 2)->update('test', array('field2' => 115, 'field3' => 116));

//$db->select('*')->from('test')->where('field1', 4)->get();