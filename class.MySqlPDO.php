<?php

    /**
     * PDO (MySQL) wrapper
     *
     * @filesource
     */

    /* First check and make sure php module pdo_mysql is installed */

    if ( !extension_loaded( 'pdo_mysql' ) ) {
        throw new Exception( 'MySqlPDO requires mysql_pdo module installed!' );
    }


    /**
     * MySqlPDO: A wrapper and utility class using PDO (MySQL)
     *
     * @author Anjan Bhowmik
     */
    class MySqlPDO {

        /**
         * Private static copy for singleton system
         *
         * @var MySqlPDO $_oInstance
         */

        private static $_oInstance = NULL;

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
         * The name of the database passed to connect() method
         *
         * @var string
         */

        private $_sDatabaseName = NULL;

        /**
         * Protected constructor
         */

        protected function __construct() {}


        /**
         * Prepares a SELECT sql query to fetch data from a single table
         *
         * @param string $tableName    A single table name or a coma separated list of table names for join
         * @param string $fields       A coma separated list of valid field names. [Default : '*']
         * @param string $filterClause A string presenting the where clause. Supports parameters for prepared
         *                             statements
         * @param string $orderClause  An array of sortable columns in key/value pairs. E.g. array('name' =>
         *                             'asc','email => 'desc')
         * @param int    $offset       Row offset for paging. Passing a negative value will disable paging
         * @param int    $limit        Number of rows to return in each page. Ignored if offset is negative [Default :
         *                             10]
         *
         * @return bool|string
         */

        public function prepareSqlQueryForSelect( $tableName = '', $fields = '*', $filterClause = '', $orderClause = '', $offset = -1, $limit = 10 ) {

            $tableName = trim( $tableName );

            if ( $tableName == '' ) {
                return FALSE;
            }

            $fields = trim( $fields );

            if ( $fields == '' ) {
                $fields = '*';
            }

            $sql = "select {$fields} from {$tableName}";

            if ( $filterClause != '' ) {

                $sql .= " where {$filterClause}";

            }

            $orderClause = trim( $orderClause );

            if ( $orderClause != '' ) {

                $sql .= " order by {$orderClause}";

            }

            # offset and limit values

            $_t = array();

            $offset = (int) $offset;

            if ( $offset > 0 ) {
                $_t[] = $offset;
            }

            $limit = (int) $limit;

            if ( $limit > 0 ) {
                $_t[] = $limit;
            }

            if ( !empty( $limit ) ) {


                $sql .= " limit ".join( ',', $_t );

            }

            return $sql;

        }

        /**
         * Connect to database with given info
         *
         * @param string $database   Database Name
         * @param string $user       User Name
         * @param string $password   The Password
         * @param string $host       MySQL Server name [default: localhost]
         * @param int    $port       Port number [default: 3306]
         * @param array  $pdoOptions PDO Driver options, if required
         */

        public function connect( $database = '', $user = 'root', $password = '', $host = 'localhost', $port = 3306, $pdoOptions = array() ) {

            if ( !is_array( $pdoOptions ) ) {
                $pdoOptions = array();
            }

            // Use UTF-8 encoding

            $pdoOptions[ PDO::MYSQL_ATTR_INIT_COMMAND ] = "SET NAMES 'UTF8'";

            $this->_oPdo = new PDO( "mysql:host={$host};dbname={$database};port={$port};charset=UTF8", $user, $password, $pdoOptions );

            // Store teh database name for later use

            $this->_sDatabaseName = $database;

            // set fetch mode to associated array

            $this->setArrayFetchMode( 'assoc' );

            // Set error mode to exception. Silent mode is default though

            $this->setErrorMode( PDO::ERRMODE_EXCEPTION );

        }

        /**
         * Create a new instance. for situations when you need a different connection
         *
         * @return MySqlPDO
         */

        public static function createNewInstance() {

            return new MySqlPDO();
        }

        /**
         * Delete row(s) of data from given table
         *
         * @param string $tableName   The table name
         * @param string $whereClause The where clause with parameters
         * @param array  $whereBind   Parameter values for where clause
         *
         * @return int
         */

        public function deleteData( $tableName = '', $whereClause = '', $whereBind = array() ) {

            $tableName = trim( $tableName );

            $whereClause = trim( $whereClause );

            if ( $tableName == '' ) {
                return NULL;
            }

            if ( $whereClause == '' ) {
                return NULL;
            }

            $sql = "delete from `{$tableName}` where ".$whereClause;

            return $this->executeNonQuery( $sql, $whereBind );

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

        public function disableForeignKeyCheck( $disabled = TRUE ) {

            $disabled = $disabled ? 0 : 1;

            $sql = "SET FOREIGN_KEY_CHECKS = $disabled";

            return $this->executeNonQuery( $sql );

        }

        /**
         * Check for the existence of the given value in the table column
         *
         * <p>
         * The search is performed for an exact match using = operator.
         * </p>
         *
         * @param string $tableName   The table name
         * @param string $columnName  The column name
         * @param string $columnValue The column value
         *
         * @return bool|null    Null is returned if table or column name is not provided. Else bool is returned.
         */

        public function columnHasValue( $tableName = '', $columnName = '', $columnValue = '' ) {

            $tableName = trim( $tableName );
            $columnName = trim( $columnName );

            if ( $tableName == '' || $columnName == '' ) {
                return NULL;
            }

            $sql = "select count(*) from `{$tableName}` where `{$columnName}` = ?";

            $count = (int) $this->getScaler( $sql, array( $columnValue ) );

            return $count > 0;

        }


        /**
         * Execute a DDL statement (E.g. insert, update, delete, truncate) and returns
         * affected row number
         *
         * @param string $sql        The sql query
         * @param array  $bind       Parameter values
         * @param array  $pdoOptions PDO driver options
         *
         * @return int
         */

        public function executeNonQuery( $sql = '', $bind = array(), $pdoOptions = array() ) {

            $this->_oLastStatement = $this->_oPdo->prepare( $sql, $pdoOptions );

            $res = $this->_oLastStatement->execute( $bind );

            if ( $res ) {
                return $this->_oLastStatement->rowCount();
            }

            return FALSE;

        }

        /**
         * Executes the provided sql query as it is. It DOES not use prepared statement system.
         *
         * <p>
         * This function is similar to ExecuteNonQuery(), except it will execute the sql query immediately without
         * further processing. This function will come handy, when you need to prepare a query, that does not fit nicely
         * into prepared query system.
         * </p>
         *
         * <p>
         * The DDL queries executed through this function does not make use of prepared query system. So, any data
         * passed within the query must be properly quoted by yourself using quote()
         * </p>
         *
         * @see MySqlPDO::executeNonQuery() ExecuteNonQuery()
         *
         * @param string $sql The sql query
         *
         * @return int
         */

        public function executeNonQueryDirect( $sql ) {

            return $this->_oPdo->exec( $sql );

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
         * @example examples/get_array.php
         *
         * @see     MySqlPDO::getArrayList()        GetArrayList()
         * @see     MySqlPDO::setArrayFetchMode()   SetArrayFetchMode()
         *
         * @param string $sql        The sql statement. It will be prepared
         * @param array  $bind       The parameters to be bound (if any)
         * @param array  $pdoOptions Extra PDO driver options
         *
         * @return array
         */

        public function getArray( $sql = '', $bind = array(), $pdoOptions = array() ) {

            $rows = $this->getArrayList( $sql, $bind, $pdoOptions );

            if ( empty( $rows ) ) {
                return array();
            }

            return current( $rows );

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
         * @see     MySqlPDO::getArray()            GetArray()
         * @see     MySqlPDO::setArrayFetchMode()   SetArrayFetchMode()
         *
         * @param string $sql        The sql statement. It will be prepared
         * @param array  $bind       The parameters to be bound (if any)
         * @param array  $pdoOptions Extra PDO driver options
         *
         * @return array
         */

        public function getArrayList( $sql = '', $bind = array(), $pdoOptions = array() ) {

            $this->_oLastStatement = $this->_oPdo->prepare( $sql, $pdoOptions );

            $this->_oLastStatement->execute( $bind );

            $rows = $this->_oLastStatement->fetchAll( $this->_oPdo->getAttribute( PDO::ATTR_DEFAULT_FETCH_MODE ) );

            $this->_oLastStatement->closeCursor();

            return $rows;
        }

        /**
         * Gets the average value for a column
         *
         * @param $table
         * @param $column
         *
         * @return bool|null|string
         */

        public function getAvgColumnValue( $table = '', $column = '' ) {

            $table = trim( $table );
            $column = trim( $column );

            if ( $table == '' || $column == '' ) {
                return FALSE;
            }

            $sql = "select avg(`{$column}`) from `{$table}`";

            return $this->getScaler( $sql );

        }

        /**
         * Execute the query and get a column of data
         *
         * <p>
         * The function executes the query and instead of returning rows of data set, it simply
         * returns the data for a particular column as a simple php array with numeric index.
         * </p>
         *
         * @param string $sql        The SQL query
         * @param array  $bind       Values for query parameters
         * @param int    $colIndex   The index of column [Default: 0]
         * @param array  $pdoOptions PDO driver options
         *
         * @return array
         */

        public function getColumn( $sql = '', $bind = array(), $colIndex = 0, $pdoOptions = array() ) {

            $colIndex = (int) $colIndex;

            if ( $colIndex < 0 ) {
                $colIndex = 0;
            }

            $this->_oLastStatement = $this->_oPdo->prepare( $sql, $pdoOptions );

            $this->_oLastStatement->execute( $bind );

            $rows = $this->_oLastStatement->fetchAll( PDO::FETCH_COLUMN, $colIndex );

            $this->_oLastStatement->closeCursor();

            return $rows;
        }

        /**
         * Returns the underlying PDO object
         *
         * @return PDO
         */

        public function getPdo() {

            return $this->_oPdo;
        }

        /**
         * Get instance in singleton style
         *
         * @return MySqlPDO
         */

        public static function getInstance() {

            if ( self::$_oInstance === NULL ) {
                self::$_oInstance = new MySqlPDO();
            }

            return self::$_oInstance;

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
         * @param string $sql        The sql query
         * @param array  $bind       Query parameter values
         * @param array  $pdoOptions PDO Driver options
         *
         * @return array
         */

        public function getKeyValuePairs( $sql, $bind = array(), $pdoOptions = array() ) {

            $this->_oLastStatement = $this->_oPdo->prepare( $sql, $pdoOptions );

            $this->_oLastStatement->execute( $bind );

            $rows = $this->_oLastStatement->fetchAll( PDO::FETCH_KEY_PAIR );

            $this->_oLastStatement->closeCursor();

            return $rows;

        }

        /**
         * Get last statement object executed
         *
         * @return PDOStatement
         */

        public function getLastStatement() {

            return $this->_oLastStatement;
        }

        /**
         * Gets auto increment id generated in last insert query
         *
         * @return string
         */

        public function getLastInsertId() {

            return $this->_oPdo->lastInsertId();

        }

        /**
         * Gets the max value for a column
         *
         * @param string $table
         * @param string $column
         *
         * @return bool|null|string
         */

        public function getMaxColumnValue( $table = '', $column = '' ) {

            $table = trim( $table );
            $column = trim( $column );

            if ( $table == '' || $column == '' ) {
                return FALSE;
            }

            $sql = "select max(`{$column}`) from `{$table}`";

            return $this->getScaler( $sql );

        }

        /**
         * Gets the minimum value for a column
         *
         * @param string $table
         * @param string $column
         *
         * @return mixed
         */

        public function getMinColumnValue( $table = '', $column = '' ) {

            $table = trim( $table );
            $column = trim( $column );

            if ( $table == '' || $column == '' ) {
                return FALSE;
            }

            $sql = "select min(`{$column}`) from `{$table}`";

            return $this->getScaler( $sql );

        }

        /**
         * Executes the sql query and get the first row as object
         *
         * <p>
         * It is similar to <b>GetObjectList()</b>, the difference is that it only returns
         * the first row as object.
         * </p>
         *
         * @see MySqlPDO::getObjectList() GetObjectList()
         *
         * @param string $sql        The sql query to be used in prepared statement.
         * @param array  $bind       The parameter values for the query.
         * @param string $className  The PHP class name. The class must be defined.
         * @param array  $ctorArgs   If the class constructor requires parameters, pass here.
         * @param array  $pdoOptions Extra PDO driver options.
         *
         * @return object|null Returns null if record set is empty.
         */

        public function getObject( $sql = '', $bind = array(), $className = 'stdClass', $ctorArgs = array(), $pdoOptions = array() ) {

            $list = $this->getObjectList( $sql, $bind, $className, $ctorArgs, $pdoOptions );

            if ( empty( $list ) ) {
                return NULL;
            }

            return current( $list );

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
         * @param string $sql        The sql query to be used in prepared statement.
         * @param array  $bind       The parameter values for the query.
         * @param string $className  The PHP class name. The class must be defined.
         * @param array  $ctorArgs   If the class constructor requires parameters, pass here.
         * @param array  $pdoOptions Extra PDO driver options.
         *
         * @return array
         */

        public function getObjectList( $sql = '', $bind = array(), $className = 'stdClass', $ctorArgs = array(), $pdoOptions = array() ) {

            $this->_oLastStatement = $this->_oPdo->prepare( $sql, $pdoOptions );

            $this->_oLastStatement->execute( $bind );

            $rows = $this->_oLastStatement->fetchAll( PDO::FETCH_CLASS, $className, $ctorArgs );

            $this->_oLastStatement->closeCursor();

            return $rows;

        }

        /**
         * Executes the query and gets a single string value
         *
         * <p>
         * This function executes the query. Then if the result set is empty returns null, else it will
         * return the value of first column of the row. In other words, you get the value of first column
         * of the first row as string.
         * </p>
         *
         * @param string $sql        The sql query to be used in prepared statement.
         * @param array  $bind       The parameter values for the query.
         * @param array  $pdoOptions Extra PDO driver options.
         *
         * @return string|null
         */

        public function getScaler( $sql = '', $bind = array(), $pdoOptions = array() ) {

            $oldFetchMode = $this->_oPdo->getAttribute( PDO::ATTR_DEFAULT_FETCH_MODE );

            $this->setArrayFetchMode( 'num' );

            $row = $this->getArray( $sql, $bind, $pdoOptions );

            if ( empty( $row ) ) {
                return NULL;
            }

            $this->_oPdo->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, $oldFetchMode );

            return $row[ 0 ];

        }

        /**
         * Gets the sum for column
         *
         * @param string $table  Name of table
         * @param string $column Column name
         *
         * @return mixed
         */

        public function getTotalColumnValue( $table = '', $column = '' ) {

            $table = trim( $table );
            $column = trim( $column );

            if ( $table == '' || $column == '' ) {
                return FALSE;
            }

            $sql = "select sum(`{$column}`) from `{$table}`";

            return $this->getScaler( $sql );

        }

        /**
         * Gets detailed column info for the given table
         *
         * <p>
         * This function fetches column info like: name, type, size, etc. for the given table. This is similar to
         * GetTableColumnNames() except it returns much more info than just names. The column data list is sorted by
         * ordinal position or the position in which they are defined in the table.
         * </p>
         *
         * @see MySqlPDO::getTableColumnNames()  GetTableColumnNames()
         *
         * @param string $tableName    Name of table.
         * @param string $databaseName Name of database containing the table. if empty, current database is used
         *
         * @return array
         */

        public function getTableColumnList( $tableName = '', $databaseName = '' ) {

            $tableName = trim( $tableName );
            $databaseName = trim( $databaseName );

            if ( $tableName == '' ) {
                return array();
            }

            if ( $databaseName == '' ) {
                $databaseName = $this->_sDatabaseName;
            }


            $sql = "select * from information_schema.COLUMNS where TABLE_SCHEMA = ? and TABLE_NAME = ? order by ORDINAL_POSITION";

            return $this->getArrayList( $sql, array( $databaseName, $tableName ) );

        }

        /**
         * Gets list of column names from a table
         *
         * <p>
         * Using this function you can retrieve a list of column names from any table in any database the connecting
         * user has permission on.
         * </p>
         *
         * @param string $tableName    Name of table.
         * @param string $databaseName Name of database containing the table. if empty, current database is used
         * @param string $sortBy       Sort column names by any of ["name","position"] (Default: "position")
         *
         * @return array
         */

        public function getTableColumnNames( $tableName = '', $databaseName = '', $sortBy = 'position' ) {

            $tableName = trim( $tableName );
            $databaseName = trim( $databaseName );

            if ( $tableName == '' ) {
                return array();
            }

            if ( $databaseName == '' ) {
                $databaseName = $this->_sDatabaseName;
            }


            $sql = "select COLUMN_NAME from information_schema.COLUMNS where TABLE_SCHEMA = ? and TABLE_NAME = ? order by ";

            switch ( $sortBy ) {

                case 'name':
                    $sql .= "COLUMN_NAME";
                    break;
                case 'position':
                default:
                    $sql .= "ORDINAL_POSITION";
                    break;

            }

            return $this->getColumn( $sql, array( $databaseName, $tableName ), 0 );

        }

        /**
         * Returns list of table names in the given database.
         *
         * <p>
         * This function returns a list of available tables in the given database. the list is sorted by
         * table name. If no database name is provided, the current open database name is used.
         * </p>
         *
         * @param string $databaseName The name of database. If empty, uses the current database name.
         *
         * @return array
         */

        public function getTableNames( $databaseName = '' ) {

            $databaseName = trim( $databaseName );

            if ( $databaseName == '' ) {
                $databaseName = $this->_sDatabaseName;
            }

            $sql = "select TABLE_NAME from information_schema.TABLES where TABLE_SCHEMA = ? and TABLE_TYPE = 'BASE TABLE' order by TABLE_NAME";

            return $this->getColumn( $sql, array( $databaseName ), 0 );

        }

        /**
         * Inserts a row of data into a table
         *
         * <p>
         * Inserts a single row of data into a table. After insert if required use MySqlPDO::GetLastInsertId() to fetch
         * the generated auto inc. column value.
         * </p>
         *
         * @param string $tableName The name of the table to insert data
         * @param array  $data      The data as key/value pairs. E.g. array('column1' => 'data1','column2' => 'data2',
         *                          ...)
         *
         * @return bool|int Returns false if no table name or data specified. Returns 0 if insert fails. Else returns 1
         */

        public function insertData( $tableName = '', $data = array() ) {

            $tableName = trim( $tableName );

            if ( $tableName == '' ) {
                return FALSE;
            }

            if ( is_array( $data ) && count( $data ) > 0 ) {

                $fields = array();
                $params = array();
                $values = array();

                foreach ( $data as $k => $v ) {

                    $fields[] = "`$k`";
                    $params[] = '?';
                    $values[] = $v;

                }

                $sql = "insert into `{$tableName}` (".join( ', ', $fields ).") values (".join( ', ', $params ).")";

                return $this->executeNonQuery( $sql, $values );

            }

            return FALSE;
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
         * @param   string $query  The sql query with parameter placeholders
         * @param   array  $params The array of substitution parameters
         *
         * @return  string The interpolated query
         *
         * @link http://stackoverflow.com/questions/210564/getting-raw-sql-query-string-from-pdo-prepared-statements
         *       The original article in stackoverflow about InterpolateQuery()
         */

        public static function interpolateQuery( $query = '', $params = array() ) {

            $_pdo = self::getInstance()->getPdo();

            $keys = array();

            if ( !empty( $params ) ) {

                # build a regular expression for each parameter
                foreach ( $params as $key => &$value ) {

                    if ( is_string( $key ) ) {
                        $keys[] = '/:'.$key.'/';
                    }
                    else {
                        $keys[] = '/[?]/';
                    }

                    $value = $_pdo->quote( $value, PDO::PARAM_STR );
                }

                $query = preg_replace( $keys, $params, $query, 1, $count );
            }


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
         * @param string $sql        The sql query
         * @param array  $bind       Query parameter values
         * @param array  $pdoOptions PDO driver options
         *
         * @return PDOStatement
         */

        public function query( $sql = '', $bind = array(), $pdoOptions = array() ) {

            $this->_oLastStatement = $this->_oPdo->prepare( $sql, $pdoOptions );

            $this->_oLastStatement->execute( $bind );

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
         * @see MySqlPDO::getArray()        GetArray()
         * @see MySqlPDO::getArrayList()    GetArrayList()
         *
         * @param string $mode
         */

        public function setArrayFetchMode( $mode = 'assoc' ) {

            if ( $this->_oPdo ) {

                switch ( $mode ) {
                    case 'num':
                        $this->_oPdo->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_NUM );
                        break;
                    case 'both':
                        $this->_oPdo->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_BOTH );
                        break;
                    case 'assoc':
                    default:
                        $this->_oPdo->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
                        break;

                }

            }

        }

        /**
         * Selects data as a list of array from the given table(s)
         *
         * <p>
         * This function builds upon GetArrayList() and provides a shorter way to fetch data from a single table
         * or a list of tables using implicit join. Sing it uses GetArrayList() you can use SetArrayFetchMode()
         * to set the array index type to one of ['assoc','num','both']
         * </p>
         *
         * @see MySqlPDO::getArrayList()            GetArrayList()
         * @see MySqlPDO::setArrayFetchMode()       SetArrayFetchMode()
         *
         * @param string $tableName    A single table name or a coma separated list of table names for join
         * @param string $fields       A coma separated list of valid field names. [Default : '*']
         * @param string $filterClause A string presenting the where clause. Supports parameters for prepared
         *                             statements
         * @param array  $filterBind   Values for bound query parameters
         * @param array  $sortBy       An array of sortable columns in key/value pairs. E.g. array('name' =>
         *                             'asc','email => 'desc')
         * @param int    $offset       Row offset for paging. Passing a negative value will disable paging
         * @param int    $limit        Number of rows to return in each page. Ignored if offset is negative [Default :
         *                             10]
         * @param array  $pdoOptions   Extra PDO driver options
         *
         * @return array
         */

        public function selectArrayListFromTable( $tableName = '', $fields = '*', $filterClause = '', $filterBind = array(), $sortBy = array(), $offset = -1, $limit = 10, $pdoOptions = array() ) {

            $sql = $this->prepareSqlQueryForSelect( $tableName, $fields, $filterClause, $sortBy, $offset, $limit );

            return $this->getArrayList( $sql, $filterBind, $pdoOptions );

        }

        /**
         * Selects data as a list of objects from the given table(s)
         *
         * <p>
         * This functions works same as SelectArrayListFromTable(), except it returns list of object instead of
         * array.
         * </p>
         *
         * @see MySqlPDO::selectArrayListFromTable()    SelectArrayListFromTable()
         *
         * @param string $tableName    A single table name or a coma separated list of table names for join
         * @param string $fields       A coma separated list of valid field names. [Default : '*']
         * @param string $filterClause A string presenting the where clause. Supports parameters for prepared
         *                             statements
         * @param array  $filterBind   Values for bound query parameters
         * @param array  $sortBy       An array of sortable columns in key/value pairs. E.g. array('name' =>
         *                             'asc','email => 'desc')
         * @param int    $offset       Row offset for paging. Passing a negative value will disable paging
         * @param int    $limit        Number of rows to return in each page. Ignored if offset is negative [Default :
         *                             10]
         * @param string $className    The name of teh class. the class must exist. [Default: 'stdClass']
         * @param array  $ctorArgs     The class __construct() parameters
         * @param array  $pdoOptions   Extra PDO driver options
         *
         * @return array
         */


        public function selectObjectListFromTable( $tableName, $fields = '*', $filterClause = '', $filterBind = array(), $sortBy = array(), $offset = -1, $limit = 10, $className = 'stdClass', $ctorArgs = array(), $pdoOptions = array() ) {

            $sql = $this->prepareSqlQueryForSelect( $tableName, $fields, $filterClause, $sortBy, $offset, $limit );

            return $this->getObjectList( $sql, $filterBind, $className, $ctorArgs, $pdoOptions );
        }

        /**
         * Sets error mode. based on this settings you can control whether to display error messages for debugging or
         * just silently ignore the error and pretend no data was returned.
         *
         * @param int $mode
         *
         * @return bool
         */

        public function setErrorMode( $mode = PDO::ERRMODE_SILENT ) {

            return $this->_oPdo->setAttribute( PDO::ATTR_ERRMODE, $mode );
        }


        /**
         * Truncates the given table
         *
         * <p>
         * This will will attempt to empty the table and reset auto inc. counter to 0. but
         * if any foreign key check fails, the truncate would stop at that row. If you need
         * to bypass this foreign key check, you can use MySqlPDO::DisableForeignKeyCheck()
         * </p>
         *
         * @see MySqlPDO::disableForeignKeyCheck() DisableForeignKeyCheck()
         *
         * @param string $tableName The table name
         *
         * @return bool
         */

        public function truncateTable( $tableName ) {

            $tableName = trim( $tableName );

            if ( $tableName == '' ) {
                return FALSE;
            }

            return $this->executeNonQuery( "truncate {$tableName}" );

        }

        /**
         * Updates row(s) of data in given table
         *
         * @param string $tableName   The table name
         * @param array  $updateData  The key/value pairs of data.
         * @param string $whereClause The where clause with parameters
         * @param array  $whereBind   Parameter values for where clause
         *
         * @return mixed
         */

        public function updateData( $tableName = '', $updateData = array(), $whereClause = '', $whereBind = array() ) {

            $tableName = trim( $tableName );
            $whereClause = trim( $whereClause );

            if ( $tableName == '' ) {
                return NULL;
            }

            if ( !is_array( $updateData ) || count( $updateData ) == 0 ) {
                return NULL;
            }

            if ( $whereClause == '' ) {
                return NULL;
            }

            $sql = "update `{$tableName}` set ";

            $bind = array();

            # update data

            $temp = array();

            foreach ( $updateData as $k => $v ) {

                $temp[] = "`$k` = ?";
                $bind[] = $v;

            }

            $sql .= join( ', ', $temp );

            $sql .= " where {$whereClause}";

            foreach ( $whereBind as $val ) {
                $bind[] = $val;
            }

            return $this->executeNonQuery( $sql, $bind );

        }

    }