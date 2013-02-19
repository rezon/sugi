# Database

### DriverInterface

Sugi\Database\DriverInterface is the first level of abstraction. On this level there are several database drivers for different databases like MySQL, PosgreSQL, SQLite, etc. Each database driver that implements this interface is giving us the ability to use same functions to connect to the server and access the data in an uniform way. This includes few basic operations like:
 - open()
 - close()
 - query()
 - fetch()
 - escape()
and several others.

On construction you should pass connection settings or a database handle. If some of the required parameters are missing an Sugi\Database\Exception will be thrown. If a handle parameter is given it will be used, instead of creating new database connection. If a handle is a wrong type a Sugi\Database\Exception will be thrown. Note that no connection will be automatically established - it requires additional open() call.

open() method will throw an Sugi\Database\Exception if a connection problem occur.

close() method frees database handler. After closing connection you can make another one with open() method only if a connection settings are set on creation.

query() method is used for all CRUD routines. If a query fails the method will return FALSE. You can make additional call to the error() method to check what was wrong with the SQL query.

And finally one note. You can use these drivers directly, but since they are very lightweight, they are limited in what they do. Instead you should use Sugi\Database to access much more functionality.

### Sugi\Database

Sugi\Database acts as a second level of abstraction. It uses DriverInterface drivers as a base and extends functionality.
 - construct providing a DriverInterface or the Database can factory itself giving one array as a parameter.
 - database connection is not established on creation. This give you ability to instantiate Sugi\Database very early in the application.
 - connection is automatically established when it's really necessary - first time when you execute any database operation.
 - database functions specific to the type of the server can be accessed via their original names.
 - on close() the database handle is freed

### Sugi\Database\Exception

Each database has it's own exception, warning and error routines, so we need one standard way do deal with them.
Sugi\Database\Exception defines an exception type:
 - internal_error - typically on Sugi\Database creation - missing or invalid parameters, wrong database driver type, invalid handles, etc.
 - connection_error - database connection errors
 - sql_error - errors in database queries
 - resource_error - errors while fetching data, etc. providing wrong resource, typically after mismatched SQL queries 
