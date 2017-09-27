MySqlPDO
========

A wrapper for MySQL PDO

Ths function provides wrapper for most used PDO functionalities, like: preparing and executing statements and fetching data.

Sample Data
===========

Download sample employees data from: https://github.com/datacharmer/test_db/archive/master.zip

All queries in sample will be performed using that sample database. A trimmed down version of the database dump is also available inside here: `examples/sql/employees.sql`

# API Documentation

## Table of Contents

* [MySqlPDO](#mysqlpdo)
    * [prepareSqlQueryForSelect](#preparesqlqueryforselect)
    * [connect](#connect)
    * [createNewInstance](#createnewinstance)
    * [deleteData](#deletedata)
    * [disableForeignKeyCheck](#disableforeignkeycheck)
    * [columnHasValue](#columnhasvalue)
    * [executeNonQuery](#executenonquery)
    * [executeNonQueryDirect](#executenonquerydirect)
    * [getArray](#getarray)
    * [getArrayList](#getarraylist)
    * [getAvgColumnValue](#getavgcolumnvalue)
    * [getColumn](#getcolumn)
    * [getPdo](#getpdo)
    * [getInstance](#getinstance)
    * [getKeyValuePairs](#getkeyvaluepairs)
    * [getLastStatement](#getlaststatement)
    * [getLastInsertId](#getlastinsertid)
    * [getMaxColumnValue](#getmaxcolumnvalue)
    * [getMinColumnValue](#getmincolumnvalue)
    * [getObject](#getobject)
    * [getObjectList](#getobjectlist)
    * [getScaler](#getscaler)
    * [getTotalColumnValue](#gettotalcolumnvalue)
    * [getTableColumnList](#gettablecolumnlist)
    * [getTableColumnNames](#gettablecolumnnames)
    * [getTableNames](#gettablenames)
    * [insertData](#insertdata)
    * [interpolateQuery](#interpolatequery)
    * [query](#query)
    * [setArrayFetchMode](#setarrayfetchmode)
    * [selectArrayListFromTable](#selectarraylistfromtable)
    * [selectObjectListFromTable](#selectobjectlistfromtable)
    * [setErrorMode](#seterrormode)
    * [truncateTable](#truncatetable)
    * [updateData](#updatedata)

## MySqlPDO

MySqlPDO: A wrapper and utility class using PDO (MySQL)



* Full name: \MySqlPDO


### prepareSqlQueryForSelect

Prepares a SELECT sql query to fetch data from a single table

```php
MySqlPDO::prepareSqlQueryForSelect( string $tableName = &#039;&#039;, string $fields = &#039;*&#039;, string $filterClause = &#039;&#039;, string $orderClause = &#039;&#039;, integer $offset = -1, integer $limit = 10 ): boolean|string
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tableName` | **string** | A single table name or a coma separated list of table names for join |
| `$fields` | **string** | A coma separated list of valid field names. [Default : '*'] |
| `$filterClause` | **string** | A string presenting the where clause. Supports parameters for prepared
                            statements |
| `$orderClause` | **string** | An array of sortable columns in key/value pairs. E.g. array('name' =>
                            'asc','email => 'desc') |
| `$offset` | **integer** | Row offset for paging. Passing a negative value will disable paging |
| `$limit` | **integer** | Number of rows to return in each page. Ignored if offset is negative [Default :
                            10] |




---

### connect

Connect to database with given info

```php
MySqlPDO::connect( string $database = &#039;&#039;, string $user = &#039;root&#039;, string $password = &#039;&#039;, string $host = &#039;localhost&#039;, integer $port = 3306, array $pdoOptions = array() )
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$database` | **string** | Database Name |
| `$user` | **string** | User Name |
| `$password` | **string** | The Password |
| `$host` | **string** | MySQL Server name [default: localhost] |
| `$port` | **integer** | Port number [default: 3306] |
| `$pdoOptions` | **array** | PDO Driver options, if required |




---

### createNewInstance

Create a new instance. for situations when you need a different connection

```php
MySqlPDO::createNewInstance(  ): \MySqlPDO
```



* This method is **static**.



---

### deleteData

Delete row(s) of data from given table

```php
MySqlPDO::deleteData( string $tableName = &#039;&#039;, string $whereClause = &#039;&#039;, array $whereBind = array() ): integer
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tableName` | **string** | The table name |
| `$whereClause` | **string** | The where clause with parameters |
| `$whereBind` | **array** | Parameter values for where clause |




---

### disableForeignKeyCheck

Instructs MySql to disable/enable foreign key check

```php
MySqlPDO::disableForeignKeyCheck( boolean $disabled = TRUE ): integer
```

<p>
If foreign key check is disabled, records from parent table can be deleted,
even if dependent rows in child tables exist. But this will create orphaned
records.
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$disabled` | **boolean** |  |




---

### columnHasValue

Check for the existence of the given value in the table column

```php
MySqlPDO::columnHasValue( string $tableName = &#039;&#039;, string $columnName = &#039;&#039;, string $columnValue = &#039;&#039; ): boolean|null
```

<p>
The search is performed for an exact match using = operator.
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tableName` | **string** | The table name |
| `$columnName` | **string** | The column name |
| `$columnValue` | **string** | The column value |


**Return Value:**

Null is returned if table or column name is not provided. Else bool is returned.



---

### executeNonQuery

Execute a DDL statement (E.g. insert, update, delete, truncate) and returns
affected row number

```php
MySqlPDO::executeNonQuery( string $sql = &#039;&#039;, array $bind = array(), array $pdoOptions = array() ): integer
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sql` | **string** | The sql query |
| `$bind` | **array** | Parameter values |
| `$pdoOptions` | **array** | PDO driver options |




---

### executeNonQueryDirect

Executes the provided sql query as it is. It DOES not use prepared statement system.

```php
MySqlPDO::executeNonQueryDirect( string $sql ): integer
```

<p>
This function is similar to ExecuteNonQuery(), except it will execute the sql query immediately without
further processing. This function will come handy, when you need to prepare a query, that does not fit nicely
into prepared query system.
</p>

<p>
The DDL queries executed through this function does not make use of prepared query system. So, any data
passed within the query must be properly quoted by yourself using quote()
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sql` | **string** | The sql query |



**See Also:**

* \MySqlPDO::executeNonQuery() - ExecuteNonQuery()

---

### getArray

Executes a SELECT query and gets first row of result as array

```php
MySqlPDO::getArray( string $sql = &#039;&#039;, array $bind = array(), array $pdoOptions = array() ): array
```

<p>
This function works similarly as <b>GetArrayList</b> taking sql query
and bound parameter values (if any). The difference is it only returns
the first row. Use this when you are looking for just one record.
</p>

<p>
The array fetch mode can be set using <b>SetArrayFetchMode()</b> to be
associative, numeric or both.
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sql` | **string** | The sql statement. It will be prepared |
| `$bind` | **array** | The parameters to be bound (if any) |
| `$pdoOptions` | **array** | Extra PDO driver options |



**See Also:**

* \MySqlPDO::getArrayList() - GetArrayList()* \MySqlPDO::setArrayFetchMode() - SetArrayFetchMode()

---

### getArrayList

Executes a SELECT query and gets result as list of array

```php
MySqlPDO::getArrayList( string $sql = &#039;&#039;, array $bind = array(), array $pdoOptions = array() ): array
```

<p>
Use this function to execute select queries and get result data as list
of array. The sql statement is executed as prepared statement. the <b>$bind</b>
param lets you to pass the values for parameters.
</p>

<p>
If you only need the first row from result set as array, use <b>GetArray()</b> instead.
</p>

<p>
The array fetch mode can be set using <b>SetArrayFetchMode()</b> to be
associative, numeric or both.
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sql` | **string** | The sql statement. It will be prepared |
| `$bind` | **array** | The parameters to be bound (if any) |
| `$pdoOptions` | **array** | Extra PDO driver options |



**See Also:**

* \MySqlPDO::getArray() - GetArray()* \MySqlPDO::setArrayFetchMode() - SetArrayFetchMode()

---

### getAvgColumnValue

Gets the average value for a column

```php
MySqlPDO::getAvgColumnValue(  $table = &#039;&#039;,  $column = &#039;&#039; ): boolean|null|string
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$table` | **** |  |
| `$column` | **** |  |




---

### getColumn

Execute the query and get a column of data

```php
MySqlPDO::getColumn( string $sql = &#039;&#039;, array $bind = array(), integer $colIndex, array $pdoOptions = array() ): array
```

<p>
The function executes the query and instead of returning rows of data set, it simply
returns the data for a particular column as a simple php array with numeric index.
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sql` | **string** | The SQL query |
| `$bind` | **array** | Values for query parameters |
| `$colIndex` | **integer** | The index of column [Default: 0] |
| `$pdoOptions` | **array** | PDO driver options |




---

### getPdo

Returns the underlying PDO object

```php
MySqlPDO::getPdo(  ): \PDO
```







---

### getInstance

Get instance in singleton style

```php
MySqlPDO::getInstance(  ): \MySqlPDO
```



* This method is **static**.



---

### getKeyValuePairs

Execute query and get key/value pairs

```php
MySqlPDO::getKeyValuePairs( string $sql, array $bind = array(), array $pdoOptions = array() ): array
```

<p>
This function executes the and creates an associative array with key being
the values from first column and value being the values from second column.
If there are multiple different values for a single value in first column,
only the last value from second column will be available.
</p>

<p>
The SQL query passed to this function, MUST have EXACTLY 2 columns.
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sql` | **string** | The sql query |
| `$bind` | **array** | Query parameter values |
| `$pdoOptions` | **array** | PDO Driver options |




---

### getLastStatement

Get last statement object executed

```php
MySqlPDO::getLastStatement(  ): \PDOStatement
```







---

### getLastInsertId

Gets auto increment id generated in last insert query

```php
MySqlPDO::getLastInsertId(  ): string
```







---

### getMaxColumnValue

Gets the max value for a column

```php
MySqlPDO::getMaxColumnValue( string $table = &#039;&#039;, string $column = &#039;&#039; ): boolean|null|string
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$table` | **string** |  |
| `$column` | **string** |  |




---

### getMinColumnValue

Gets the minimum value for a column

```php
MySqlPDO::getMinColumnValue( string $table = &#039;&#039;, string $column = &#039;&#039; ): mixed
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$table` | **string** |  |
| `$column` | **string** |  |




---

### getObject

Executes the sql query and get the first row as object

```php
MySqlPDO::getObject( string $sql = &#039;&#039;, array $bind = array(), string $className = &#039;stdClass&#039;, array $ctorArgs = array(), array $pdoOptions = array() ): object|null
```

<p>
It is similar to <b>GetObjectList()</b>, the difference is that it only returns
the first row as object.
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sql` | **string** | The sql query to be used in prepared statement. |
| `$bind` | **array** | The parameter values for the query. |
| `$className` | **string** | The PHP class name. The class must be defined. |
| `$ctorArgs` | **array** | If the class constructor requires parameters, pass here. |
| `$pdoOptions` | **array** | Extra PDO driver options. |


**Return Value:**

Returns null if record set is empty.


**See Also:**

* \MySqlPDO::getObjectList() - GetObjectList()

---

### getObjectList

Executes the sql query and get data as list of objects

```php
MySqlPDO::getObjectList( string $sql = &#039;&#039;, array $bind = array(), string $className = &#039;stdClass&#039;, array $ctorArgs = array(), array $pdoOptions = array() ): array
```

<p>
Like GetArrayList() this function performs the select query, but instead of array, it
returns a list of objects. If no class name is provided, <b>stdClass</b> is assumed.
</p>

<p>
PDO sets the class variables BEFORE calling the constructor. You can use this to further
process the data returned if you need
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sql` | **string** | The sql query to be used in prepared statement. |
| `$bind` | **array** | The parameter values for the query. |
| `$className` | **string** | The PHP class name. The class must be defined. |
| `$ctorArgs` | **array** | If the class constructor requires parameters, pass here. |
| `$pdoOptions` | **array** | Extra PDO driver options. |




---

### getScaler

Executes the query and gets a single string value

```php
MySqlPDO::getScaler( string $sql = &#039;&#039;, array $bind = array(), array $pdoOptions = array() ): string|null
```

<p>
This function executes the query. Then if the result set is empty returns null, else it will
return the value of first column of the row. In other words, you get the value of first column
of the first row as string.
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sql` | **string** | The sql query to be used in prepared statement. |
| `$bind` | **array** | The parameter values for the query. |
| `$pdoOptions` | **array** | Extra PDO driver options. |




---

### getTotalColumnValue

Gets the sum for column

```php
MySqlPDO::getTotalColumnValue( string $table = &#039;&#039;, string $column = &#039;&#039; ): mixed
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$table` | **string** | Name of table |
| `$column` | **string** | Column name |




---

### getTableColumnList

Gets detailed column info for the given table

```php
MySqlPDO::getTableColumnList( string $tableName = &#039;&#039;, string $databaseName = &#039;&#039; ): array
```

<p>
This function fetches column info like: name, type, size, etc. for the given table. This is similar to
GetTableColumnNames() except it returns much more info than just names. The column data list is sorted by
ordinal position or the position in which they are defined in the table.
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tableName` | **string** | Name of table. |
| `$databaseName` | **string** | Name of database containing the table. if empty, current database is used |



**See Also:**

* \MySqlPDO::getTableColumnNames() - GetTableColumnNames()

---

### getTableColumnNames

Gets list of column names from a table

```php
MySqlPDO::getTableColumnNames( string $tableName = &#039;&#039;, string $databaseName = &#039;&#039;, string $sortBy = &#039;position&#039; ): array
```

<p>
Using this function you can retrieve a list of column names from any table in any database the connecting
user has permission on.
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tableName` | **string** | Name of table. |
| `$databaseName` | **string** | Name of database containing the table. if empty, current database is used |
| `$sortBy` | **string** | Sort column names by any of ["name","position"] (Default: "position") |




---

### getTableNames

Returns list of table names in the given database.

```php
MySqlPDO::getTableNames( string $databaseName = &#039;&#039; ): array
```

<p>
This function returns a list of available tables in the given database. the list is sorted by
table name. If no database name is provided, the current open database name is used.
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$databaseName` | **string** | The name of database. If empty, uses the current database name. |




---

### insertData

Inserts a row of data into a table

```php
MySqlPDO::insertData( string $tableName = &#039;&#039;, array $data = array() ): boolean|integer
```

<p>
Inserts a single row of data into a table. After insert if required use MySqlPDO::GetLastInsertId() to fetch
the generated auto inc. column value.
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tableName` | **string** | The name of the table to insert data |
| `$data` | **array** | The data as key/value pairs. E.g. array('column1' => 'data1','column2' => 'data2',
                         ...) |


**Return Value:**

Returns false if no table name or data specified. Returns 0 if insert fails. Else returns 1



---

### interpolateQuery

Generates an actual sql query by resolving bound params

```php
MySqlPDO::interpolateQuery( string $query = &#039;&#039;, array $params = array() ): string
```

<p>
Replaces any parameter placeholders in a query with the value of that
parameter. Useful for debugging. Assumes anonymous parameters from
$params are are in the same order as specified in $query. <b>This is a
slightly modified version of original one</b>
</p>

* This method is **static**.
**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$query` | **string** | The sql query with parameter placeholders |
| `$params` | **array** | The array of substitution parameters |


**Return Value:**

The interpolated query


**See Also:**

* http://stackoverflow.com/questions/210564/getting-raw-sql-query-string-from-pdo-prepared-statements - The original article in stackoverflow about InterpolateQuery()

---

### query

Prepares a PDOStatement, calls it's execute() method and returns it.

```php
MySqlPDO::query( string $sql = &#039;&#039;, array $bind = array(), array $pdoOptions = array() ): \PDOStatement
```

<p>
This function takes your sql query and parameter values, executes that and
instead of returning the results itself, it provides you with the PDOStatement
object.
</p>

<p>
This will be quite useful in situations, where the resul tset is quite
big and can take up a lot of memory. In this case you can use the PDOStatement
and loop through the result set using PDOStatement::fetch() method.
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sql` | **string** | The sql query |
| `$bind` | **array** | Query parameter values |
| `$pdoOptions` | **array** | PDO driver options |




---

### setArrayFetchMode

Sets array fetch mode

```php
MySqlPDO::setArrayFetchMode( string $mode = &#039;assoc&#039; )
```

<p>
While fetching results as array, the fetch mode can be set to one of these three
values <b>["assoc", "both", "num"]</b>. the default value is <b>"assoc"</b>. This only
works with <b>GetArray()</b> and <b>GetArrayList()</b> functions.
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$mode` | **string** |  |



**See Also:**

* \MySqlPDO::getArray() - GetArray()* \MySqlPDO::getArrayList() - GetArrayList()

---

### selectArrayListFromTable

Selects data as a list of array from the given table(s)

```php
MySqlPDO::selectArrayListFromTable( string $tableName = &#039;&#039;, string $fields = &#039;*&#039;, string $filterClause = &#039;&#039;, array $filterBind = array(), array $sortBy = array(), integer $offset = -1, integer $limit = 10, array $pdoOptions = array() ): array
```

<p>
This function builds upon GetArrayList() and provides a shorter way to fetch data from a single table
or a list of tables using implicit join. Sing it uses GetArrayList() you can use SetArrayFetchMode()
to set the array index type to one of ['assoc','num','both']
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tableName` | **string** | A single table name or a coma separated list of table names for join |
| `$fields` | **string** | A coma separated list of valid field names. [Default : '*'] |
| `$filterClause` | **string** | A string presenting the where clause. Supports parameters for prepared
                            statements |
| `$filterBind` | **array** | Values for bound query parameters |
| `$sortBy` | **array** | An array of sortable columns in key/value pairs. E.g. array('name' =>
                            'asc','email => 'desc') |
| `$offset` | **integer** | Row offset for paging. Passing a negative value will disable paging |
| `$limit` | **integer** | Number of rows to return in each page. Ignored if offset is negative [Default :
                            10] |
| `$pdoOptions` | **array** | Extra PDO driver options |



**See Also:**

* \MySqlPDO::getArrayList() - GetArrayList()* \MySqlPDO::setArrayFetchMode() - SetArrayFetchMode()

---

### selectObjectListFromTable

Selects data as a list of objects from the given table(s)

```php
MySqlPDO::selectObjectListFromTable( string $tableName, string $fields = &#039;*&#039;, string $filterClause = &#039;&#039;, array $filterBind = array(), array $sortBy = array(), integer $offset = -1, integer $limit = 10, string $className = &#039;stdClass&#039;, array $ctorArgs = array(), array $pdoOptions = array() ): array
```

<p>
This functions works same as SelectArrayListFromTable(), except it returns list of object instead of
array.
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tableName` | **string** | A single table name or a coma separated list of table names for join |
| `$fields` | **string** | A coma separated list of valid field names. [Default : '*'] |
| `$filterClause` | **string** | A string presenting the where clause. Supports parameters for prepared
                            statements |
| `$filterBind` | **array** | Values for bound query parameters |
| `$sortBy` | **array** | An array of sortable columns in key/value pairs. E.g. array('name' =>
                            'asc','email => 'desc') |
| `$offset` | **integer** | Row offset for paging. Passing a negative value will disable paging |
| `$limit` | **integer** | Number of rows to return in each page. Ignored if offset is negative [Default :
                            10] |
| `$className` | **string** | The name of teh class. the class must exist. [Default: 'stdClass'] |
| `$ctorArgs` | **array** | The class __construct() parameters |
| `$pdoOptions` | **array** | Extra PDO driver options |



**See Also:**

* \MySqlPDO::selectArrayListFromTable() - SelectArrayListFromTable()

---

### setErrorMode

Sets error mode. based on this settings you can control whether to display error messages for debugging or
just silently ignore the error and pretend no data was returned.

```php
MySqlPDO::setErrorMode( integer $mode = \PDO::ERRMODE_SILENT ): boolean
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$mode` | **integer** |  |




---

### truncateTable

Truncates the given table

```php
MySqlPDO::truncateTable( string $tableName ): boolean
```

<p>
This will will attempt to empty the table and reset auto inc. counter to 0. but
if any foreign key check fails, the truncate would stop at that row. If you need
to bypass this foreign key check, you can use MySqlPDO::DisableForeignKeyCheck()
</p>


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tableName` | **string** | The table name |



**See Also:**

* \MySqlPDO::disableForeignKeyCheck() - DisableForeignKeyCheck()

---

### updateData

Updates row(s) of data in given table

```php
MySqlPDO::updateData( string $tableName = &#039;&#039;, array $updateData = array(), string $whereClause = &#039;&#039;, array $whereBind = array() ): mixed
```




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tableName` | **string** | The table name |
| `$updateData` | **array** | The key/value pairs of data. |
| `$whereClause` | **string** | The where clause with parameters |
| `$whereBind` | **array** | Parameter values for where clause |




---



--------
> This document was automatically generated from source code comments on 2017-09-27 using [phpDocumentor](http://www.phpdoc.org/) and [cvuorinen/phpdoc-markdown-public](https://github.com/cvuorinen/phpdoc-markdown-public)
