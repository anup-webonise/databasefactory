<?php
/**
 * Created by Anup Kale.
 * User: Anup Kale
 * Date: 8/11/13
 * Time: 4:40 PM
 * To change this template use File | Settings | File Templates.
 */
class Query
{

    private $statement;

    private static $rowCount = null;

    private $resultObject = null;

    private $resultArray = null;

    function __construct($statement)
    {
        $this->statement = $statement;
        try
        {
            $this->resultObject = $this->statement->fetchAll(PDO::FETCH_OBJ);
            foreach($this->resultObject as $object)
            {
                $this->resultArray[] = (array) $object;
            }
        }
        catch(Exception $e)
        {
            $this->free_result();
        }
    }

    public function num_rows()
    {
        if(is_object($this->statement))
        {
            return $this->statement->rowCount();
        }
        else
        {
            throw new Exception('There is no database object to get query result');
        }
    }

    public function num_fields()
    {
        if(is_object($this->statement))
        {
            return $this->statement->columnCount();
        }
        else
        {
            throw new Exception('There is no database object to get query result');
        }
    }

    public function result_array()
    {
        if(is_object($this->statement))
        {
            return $this->resultArray;
        }
        else
        {
            throw new Exception('There is no database object to get query result');
        }
    }

    public function row($count = null, $type = null)
    {
        if(is_object($this->statement))
        {
            if($count == null)
            {
                return $this->first_row($type);
            }
            else
            {
                self::$rowCount = $count;
                if($type == 'array')
                {
                    return $this->resultArray[$count];
                }
                else
                {
                    return $this->resultObject[$count];
                }
            }
        }
        else
        {
            throw new Exception('There is no database object to get query result');
        }
    }

    public function result()
    {
        if(is_object($this->statement))
        {
            return $this->resultObject;
        }
        else
        {
            throw new Exception('There is no database object to get query result');
        }
    }

    public function first_row($type = 'Object')
    {
        if(is_object($this->statement))
        {
            self::$rowCount = 0;
            if($type == 'array')
            {
                return $this->resultArray[self::$rowCount];
            }
            else
            {
                return $this->resultObject[self::$rowCount];
            }
        }
        else
        {
            throw new Exception('There is no database object to get query result');
        }
    }

    public function last_row($type = 'Object')
    {
        if(is_object($this->statement))
        {
            self::$rowCount =  $this->num_rows() - 1;

            if($type == 'array')
            {
                return $this->resultArray[self::$rowCount];
            }
            else
            {
                return $this->resultObject[self::$rowCount];
            }
        }
        else
        {
            throw new Exception('There is no database object to get query result');
        }
    }

    public function next_row($type = null)
    {
        self::$rowCount++;
        if(self::$rowCount == $this->num_rows())
        {
            self::$rowCount--;
            return null;
        }
        else
        {
            if($type == 'array')
            {
                return $this->resultArray[self::$rowCount];
            }
            else
            {
                return $this->resultObject[self::$rowCount];
            }
        }
    }

    public function previous_row($type = null)
    {
        self::$rowCount--;
        if(self::$rowCount < 0)
        {
            self::$rowCount = 0;
            return null;
        }
        else
        {
            if($type == 'array')
            {
                return $this->resultArray[self::$rowCount];
            }
            else
            {
                return $this->resultObject[self::$rowCount];
            }
        }
    }

    public function free_result()
    {
        unset($this->statement);
    }
}
