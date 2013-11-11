<?php
/**
 * Created by Anup Kale.
 * User: Anup Kale
 * Date: 7/11/13
 * Time: 3:34 PM
 * To change this template use File | Settings | File Templates.
 * DatabaseFactory has been defined as final so that it cannot be
 * extended by any other class.
 */

require_once('database.configuration.inc');

class DatabaseFactory
{
    /**
     * Variable to store single object of the DatabaseFactory
     */
    private static $dbFactory = null;

    /**
     * Construct and clone functions should not be accessible from
     * outside the class
     */
    protected function __construct() {}
    protected function __clone() {}

    /**
     * The intended use of __wakeup() is to reestablish any database
     * connections that may have been lost during serialization
     * @throws Exception on unserialzation
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Create a custom exception handler
     */
    public static function exception_handler($exception) {
        die('Uncaught exception: Something went wrong with your database connection parameters');
    }

    //Get Instances
    public static function getInstance()
    {
        if(is_null(self::$dbFactory))
        {
            // Temporarily change the PHP exception handler while we . . .
            set_exception_handler(array(__CLASS__, 'exception_handler'));

            //create PDO object.
            self::$dbFactory = new PDO($database['databaseType'].':host='.$database['hostName'].';dbname='.$database['databaseName'].';charset='.$database['characterSet'], $database['userName'], $database['password']) or die( 'Could Not Connect');

            // Change the exception handler back to whatever it was before
            restore_exception_handler();
        }
        return self::$dbFactory;
    }


}








