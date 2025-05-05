<?php
/**
 * Database Connection Class
 * Establishes a connection to the database using PDO.
 * Requires DB constants (DB_HOST, DB_USER, DB_PASS, DB_NAME) to be defined, typically in config.php.
 */

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh; // Database Handler (PDO object)
    private $stmt; // PDOStatement object
    private $error; // Stores error messages

    /**
     * Constructor establishes the database connection.
     */
    public function __construct() {
        // Set DSN (Data Source Name)
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4'; // Added utf8mb4 charset

        // Set PDO options for better error handling and fetching
        $options = [
            PDO::ATTR_PERSISTENT => true, // Optional: persistent connection
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch associative arrays by default
            PDO::ATTR_EMULATE_PREPARES => false, // Use native prepared statements
        ];

        // Create a new PDO instance
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // Log the error securely
            error_log("Database Connection Error: " . $this->error);
            // Display a generic error message to the user (avoid exposing details)
            die("Error connecting to the database. Please try again later.");
        }
    }

    /**
     * Prepare statement with SQL query.
     * @param string $query The SQL query to prepare.
     * @return $this Returns the Database object instance for method chaining.
     */
    public function query($query) {
        try {
            $this->stmt = $this->dbh->prepare($query);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Query Preparation Error: " . $this->error . " | Query: " . $query);
            // Optionally re-throw or handle more gracefully
            throw $e; // Re-throwing allows calling code to catch it if needed
        }
        return $this; // Return self for chaining ->bind()
    }

    /**
     * Bind values to the prepared statement parameters.
     * Automatically determines the PDO::PARAM_* type if not specified.
     * @param string $param Parameter placeholder (e.g., :id).
     * @param mixed $value The value to bind to the parameter.
     * @param int|null $type Explicit PDO::PARAM_* type (optional).
     * @return $this Returns the Database object instance for method chaining.
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
        return $this; // Return self for chaining more ->bind() or ->execute()
    }

    /**
     * Execute the prepared statement.
     * @return bool Returns true on success or false on failure.
     */
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // Log the error with query details if possible (be careful with sensitive data)
            error_log("Query Execution Error: " . $this->error . " | Query: " . $this->stmt->queryString);
            return false; // Indicate failure
        }
    }

    /**
     * Get result set as an array of associative arrays.
     * Executes the statement before fetching.
     * @return array|false An array containing all rows, or false on failure.
     */
    public function resultSet() {
        if ($this->execute()) {
             return $this->stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch associative array
        }
        return false; // Return false if execute failed
    }

    /**
     * Get a single record as an associative array.
     * Executes the statement before fetching.
     * @return array|false An associative array for the row, or false if no row or on failure.
     */
    public function single() {
        if ($this->execute()) {
            return $this->stmt->fetch(PDO::FETCH_ASSOC); // Fetch associative array
        }
       return false; // Return false if execute failed
    }

    /**
     * Get the row count from the last executed statement (affected rows for DELETE, INSERT, UPDATE).
     * Note: May not be reliable for SELECT on all drivers.
     * @return int The number of affected rows.
     */
    public function rowCount() {
        // rowCount is called on the PDOStatement object
        return $this->stmt->rowCount();
    }

    /**
     * Get the ID of the last inserted row.
     * @return string|false The ID of the last inserted row or sequence value, or false on failure.
     */
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }

    /**
     * Initiates a database transaction.
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    /**
     * Commits the current database transaction.
     * Use this method name in your scripts (like delete.php).
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function endTransaction() { // Keep your original name
        return $this->dbh->commit();
    }

    /**
     * Rolls back the current database transaction.
     * Use this method name in your scripts (like delete.php).
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function cancelTransaction() { // Keep your original name
        return $this->dbh->rollBack();
    }

} // --- End of Database Class ---

// Ensure no code exists outside the class definition.
?>