<?php

    /**
     * PDO (MySQL) wrapper
     *
     * @filesource
     */

    /* First check and make sure php module pdo_mysql is installed */

    if ( !extension_loaded('pdo_mysql') ) {
        die('MySqlPDO requires mysql_pdo module installed!');
    }


    /**
     * MySqlPDO: A wrapper and utility class using PDO (MySQL)
     *
     * @author Anjan Bhowmik <anjan011@gmail.com>
     */
    class MySqlPDO
    {

        /**
         * Private static copy for singleton system
         *
         * @var MySqlPDO $_oDb
         */

        private static $_oDb = NULL;

        /**
         * Last executed statement
         *
         * @var PDOStatement $_oLastStatement
         */

        private $_oLastStatement = NULL;

        /**
         * Underlying PDO object
         *
         * @var PDO $_oPdo
         */

        private $_oPdo = NULL;

        /**
         * Protected constructor
         */

        protected function __construct() { }

        /**
         * Connect to database with given info
         *
         * @param string $database Database Name
         * @param string $user User Name
         * @param string $password The Password
         * @param string $host MySQL Server name [default: localhost]
         * @param int $port Port number [default: 3306]
         * @param array $pdoOptions PDO Driver options, if required
         */

        public function Connect($database = '', $user = 'root', $password = '', $host = 'localhost', $port = 3306, $pdoOptions = array())
        {
            if ( !is_array($pdoOptions) ) {
                $pdoOptions = array();
            }

            // Use UTF-8 encoding

            $pdoOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'UTF8'";

            $this->_oPdo = new PDO("mysql:host={$host};dbname={$database};port={$port};charset=UTF8", $user, $password, $pdoOptions);

            // set fetch mode to associated array

            $this->SetArrayFetchMode('assoc');

            // Set error mode to silent. This is default though

            $this->SetErrorMode(PDO::ERRMODE_SILENT);

        }

        /**
         * Create a new instance. for situations when u need a different connection
         *
         * @return MySqlPDO
         */

        public static function CreateNewInstance()
        {
            return new MySqlPDO();
        }

        /**
         * Instructs MySql to disable/enable foreign key check
         *
         * <p>
         * If foreign key check is disabled, records from parent table can be deleted,
         * even if dependent rows in child tables exist. But this will create orphaned
         * records.
         * </p>
         *
         * @param bool $disabled
         *
         * @return int
         */

        public function DisableForeignKeyCheck($disabled = true) {

            $disabled = $disabled ? 0 : 1;

            $sql = "SET FOREIGN_KEY_CHECKS = $disabled";

            return $this->ExecuteNonQuery($sql);

        }

        /**
         * Execute a DDL statement (E.g. insert, update,delete,truncate) and returns
         * affected row number
         *
         * @param string $sql The sql query
         *
         * @return int
         */

        public function ExecuteNonQuery($sql)
        {

            return $this->_oPdo->exec($sql);

        }

        /**
         * Executes a SELECT query and gets first row of result as array
         *
         * <p>
         * This function works similarly as <b>GetArrayList</b> taking sql query
         * and bound parameter values (if any). The difference is it only returns
         * the first row. Use this when you are looking for just one record.
         * </p>
         *
         * <p>
         * The array fetch mode can be set using <b>SetArrayFetchMode()</b> to be
         * associative, numeric or both.
         * </p>
         *
         * @example phpdoc-examples/get_array.php
         *
         * @see MySqlPDO::GetArrayList() GetArrayList()
         * @see MySqlPDO::SetArrayFetchMode() SetArrayFetchMode()
         *
         * @param string $sql The sql statement. It will be prepared
         * @param array $bind The parameters to be bound (if any)
         * @param array $pdoOptions Extra PDO driver options
         *
         * @return array
         */

        public function GetArray($sql, $bind = array(), $pdoOptions = array())
        {

            $rows = $this->GetArrayList($sql, $bind, $pdoOptions);

            if ( empty($rows) ) {
                return array();
            }

            return current($rows);

        }


        /**
         * Executes a SELECT query and gets result as list of array
         *
         * <p>
         * Use this function to execute select queries and get result data as list
         * of array. The sql statement is executed as prepared statement. the <b>$bind</b>
         * param lets you to pass the values for parameters.
         * </p>
         *
         * <p>
         * If you only need the first row from result set as array, use <b>GetArray()</b> instead.
         * </p>
         *
         * <p>
         * The array fetch mode can be set using <b>SetArrayFetchMode()</b> to be
         * associative, numeric or both.
         * </p>
         *
         * @example phpdoc-examples/get_array_list.php
         *
         * @see MySqlPDO::GetArray() GetArray()
         * @see MySqlPDO::SetArrayFetchMode() SetArrayFetchMode()
         *
         * @param string $sql The sql statement. It will be prepared
         * @param array $bind The parameters to be bound (if any)
         * @param array $pdoOptions Extra PDO driver options
         *
         * @return array
         */

        public function GetArrayList($sql, $bind = array(), $pdoOptions = array())
        {

            $this->_oLastStatement = $this->_oPdo->prepare($sql, $pdoOptions);

            $this->_oLastStatement->execute($bind);

            $rows = $this->_oLastStatement->fetchAll($this->_oPdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));

            $this->_oLastStatement->closeCursor();

            return $rows;
        }

        /**
         * Execute the query and get a column of data
         *
         * <p>
         * The function executes the query and instead of returning rows of data set, it simply
         * returns the data for a particular column as a simple php array with numeric index.
         * </p>
         *
         * @param string $sql The SQL query
         * @param array $bind Values for query parameters
         * @param int $colIndex the index of column [Default: 0]
         * @param array $pdoOptions PDO driver options
         *
         * @return array
         */

        public function GetColumn($sql, $bind = array(), $colIndex = 0, $pdoOptions = array())
        {

            $colIndex = (int) $colIndex;

            if ( $colIndex < 0 ) {
                $colIndex = 0;
            }

            $this->_oLastStatement = $this->_oPdo->prepare($sql, $pdoOptions);

            $this->_oLastStatement->execute($bind);

            $rows = $this->_oLastStatement->fetchAll(PDO::FETCH_COLUMN, $colIndex);

            $this->_oLastStatement->closeCursor();

            return $rows;
        }

        /**
         * Returns the underlying PDO object
         *
         * @return PDO
         */

        public function GetConnection()
        {
            return $this->_oPdo;
        }

        /**
         * Get instance in singleton style
         *
         * @return MySqlPDO
         */

        public static function GetInstance()
        {

            if ( self::$_oDb === NULL ) {
                self::$_oDb = new MySqlPDO();
            }

            return self::$_oDb;

        }

        /**
         * Execute query and get key/value pairs
         *
         * <p>
         * This function executes the and creates an associative array with key being
         * the values from first column and value being the values from second column.
         * If there are multiple different values for a single value in first column,
         * only the last value from second column will be available.
         * </p>
         *
         * <p>
         * The SQL query passed to this function, MUST have EXACTLY 2 columns.
         * </p>
         *
         * @param string $sql The sql query
         * @param array $bind Query parameter values
         * @param array $pdoOptions PDO Driver options
         *
         * @return array
         */

        public function GetKeyValuePairs($sql, $bind = array(), $pdoOptions = array())
        {

            $this->_oLastStatement = $this->_oPdo->prepare($sql, $pdoOptions);

            $this->_oLastStatement->execute($bind);

            $rows = $this->_oLastStatement->fetchAll(PDO::FETCH_KEY_PAIR);

            $this->_oLastStatement->closeCursor();

            return $rows;

        }

        /**
         * Get last statement object executed
         *
         * @return PDOStatement
         */

        public function GetLastStatement()
        {
            return $this->_oLastStatement;
        }

        /**
         * Executes the sql query and get the first row as object
         *
         * <p>
         * It is similar to <b>GetObjectList()</b>, the difference is that it only returns
         * the first row as object.
         * </p>
         *
         * @see MySqlPDO::GetObjectList() GetObjectList()
         *
         * @param string $sql The sql query to be used in prepared statement.
         * @param array $bind The parameter values for the query.
         * @param string $className The PHP class name. The class must be defined.
         * @param array $ctorArgs If the class constructor requires parameters, pass here.
         * @param array $pdoOptions Extra PDO driver options.
         *
         * @return object|null Returns null if record set is empty.
         */

        public function GetObject($sql, $bind = array(), $className = 'stdClass', $ctorArgs = array(), $pdoOptions = array())
        {

            $list = $this->GetObjectList($sql, $bind, $className, $ctorArgs, $pdoOptions);

            if ( empty($list) ) {
                return NULL;
            }

            return current($list);

        }

        /**
         * Executes the sql query and get data as list of objects
         *
         * <p>
         * Like GetArrayList() this function performs the select query, but instead of array, it
         * returns a list of objects. If no class name is provided, <b>stdClass</b> is assumed.
         * </p>
         *
         * <p>
         * PDO sets the class variables BEFORE calling the constructor. You can use this to further
         * process the data returned if you need
         * </p>
         *
         * @example phpdoc-examples/get_object_list.php
         *
         * @param string $sql The sql query to be used in prepared statement.
         * @param array $bind The parameter values for the query.
         * @param string $className The PHP class name. The class must be defined.
         * @param array $ctorArgs If the class constructor requires parameters, pass here.
         * @param array $pdoOptions Extra PDO driver options.
         *
         * @return array
         */

        public function GetObjectList($sql, $bind = array(), $className = 'stdClass', $ctorArgs = array(), $pdoOptions = array())
        {

            $this->_oLastStatement = $this->_oPdo->prepare($sql, $pdoOptions);

            $this->_oLastStatement->execute($bind);

            $rows = $this->_oLastStatement->fetchAll(PDO::FETCH_CLASS, $className, $ctorArgs);

            $this->_oLastStatement->closeCursor();

            return $rows;

        }

        /**
         * Generates an actual sql query by resolving bound params
         *
         * <p>
         * Replaces any parameter placeholders in a query with the value of that
         * parameter. Useful for debugging. Assumes anonymous parameters from
         * $params are are in the same order as specified in $query. <b>This is a
         * slightly modified version of original one</b>
         * </p>
         *
         * @param   string $query The sql query with parameter placeholders
         * @param   array $params The array of substitution parameters
         *
         * @return  string The interpolated query
         *
         * @link http://stackoverflow.com/questions/210564/getting-raw-sql-query-string-from-pdo-prepared-statements The original article in stackoverflow
         */

        public static function InterpolateQuery($query, $params)
        {

            $keys = array();

            # build a regular expression for each parameter
            foreach ( $params as $key => &$value ) {

                if ( is_string($key) ) {
                    $keys[] = '/:' . $key . '/';
                } else {
                    $keys[] = '/[?]/';
                }

                $value = self::GetInstance()->_oPdo->quote($value, PDO::PARAM_STR);
            }

            $query = preg_replace($keys, $params, $query, 1, $count);

            return $query;
        }

        /**
         * Prepares a PDOStatement, calls it's execute() method and returns it.
         *
         * <p>
         * This function takes your sql query and parameter values, executes that and
         * instead of returning the results itself, it provides you with the PDOStatement
         * object.
         *</p>
         *
         * <p>
         * This will be quite useful in situations, where the resul tset is quite
         * big and can take up a lot of memory. In this case you can use the PDOStatement
         * and loop through the result set using PDOStatement::fetch() method.
         * </p>
         *
         * @param string $query The sql query
         * @param array $bind Query paramater values
         * @param array $pdoOptions PDO driver options
         *
         * @return PDOStatement
         */

        public function Query($query, $bind = array(), $pdoOptions = array())
        {

            $this->_oLastStatement = $this->_oPdo->prepare($query, $pdoOptions);

            $this->_oLastStatement->execute($bind);

            return $this->_oLastStatement;

        }

        /**
         * Sets array fetch mode
         *
         * <p>
         * While fetching results as array, the fetch mode can be set to one of these three
         * values <b>["assoc", "both", "num"]</b>. the default value is <b>"assoc"</b>. This only
         * works with <b>GetArray()</b> and <b>GetArrayList()</b> functions.
         * </p>
         *
         * @see MySqlPDO::GetArray() GetArray()
         * @see MySqlPDO::GetArrayList() GetArrayList()
         *
         * @param string $mode
         */

        public function SetArrayFetchMode($mode = 'assoc')
        {

            if ( $this->_oPdo ) {

                switch ( $mode ) {
                    case 'num':
                        $this->_oPdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_NUM);
                        break;
                    case 'both':
                        $this->_oPdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_BOTH);
                        break;
                    case 'assoc':
                    default:
                        $this->_oPdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                        break;

                }

            }

        }


        /**
         * Set an underlying mysql PDO object
         *
         * @param PDO $pdo
         */

        public function SetConnection($pdo)
        {
            $this->_oPdo = $pdo;
        }

        /**
         * Sets error mode. based on this settings you can control whether to display error messages for debugging or
         * just silently ignore the error and pretend no data was returned.
         *
         * @param int $mode
         */

        public function SetErrorMode($mode = PDO::ERRMODE_SILENT)
        {
            $this->_oPdo->setAttribute(PDO::ATTR_ERRMODE, $mode);
        }


        /**
         * Truncate a table or empties the table
         *
         * <p>
         * This will will attempt to empty the table and reset auto inc. counter to 0. but
         * if any foreign key check fails, the truncate would stop at that row. If you need
         * to bypass this foreign key check, you can use MySqlPDO::DisableForeignKeyCheck()
         * </p>
         *
         * @see MySqlPDO::DisableForeignKeyCheck() DisableForeignKeyCheck()
         *
         * @param string $tableName The table name
         *
         * @return bool
         */

        public function TruncateTable($tableName) {

            $tableName = trim($tableName);

            if($tableName == '') {
                return false;
            }

            return $this->ExecuteNonQuery("truncate $tableName");

        }

    }