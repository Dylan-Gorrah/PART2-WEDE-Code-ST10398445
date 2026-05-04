<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : classes/Database.php
 * Description    : Object-Oriented wrapper around MySQLi.
 *                  Provides a clean interface for prepared statements and queries.
 */

class Database {
    // ── Properties ──────────────────────────────────────────────────────────
    private $host;
    private $user;
    private $pass;
    private $dbName;
    private $conn;          // The raw MySQLi connection object
    private static $instance = null; // Singleton — only one DB connection per request

    // ── Constructor ──────────────────────────────────────────────────────────
    private function __construct() {
        $this->host   = DB_HOST;    // Defined in DBConn.php
        $this->user   = DB_USER;
        $this->pass   = DB_PASS;
        $this->dbName = DB_NAME;

        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbName);

        if ($this->conn->connect_error) {
            throw new Exception("Database connection failed: " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8");
    }

    // ── Singleton pattern — call Database::getInstance() everywhere ──────────
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // ── Return the raw MySQLi connection (for use with prepared statements) ──
    public function getConnection() {
        return $this->conn;
    }

    // ── Run a SELECT query and return all rows as an associative array ────────
    // Usage: $rows = $db->query("SELECT * FROM tblUser WHERE status = ?", "s", ["verified"]);
    public function query($sql, $types = "", $params = []) {
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Query prepare failed: " . $this->conn->error);
        }

        // Bind parameters if any were passed
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            // For INSERT/UPDATE/DELETE, get_result returns false — return affected rows
            return $stmt->affected_rows;
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();
        return $rows;
    }

    // ── Run an INSERT/UPDATE/DELETE and return affected rows ─────────────────
    public function execute($sql, $types = "", $params = []) {
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Execute prepare failed: " . $this->conn->error);
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $affected = $stmt->affected_rows;
        $lastId   = $stmt->insert_id;
        $stmt->close();

        // Return array with affected rows and last inserted ID
        return ['affected' => $affected, 'lastId' => $lastId];
    }

    // ── Escape a string safely (use prepared statements instead where possible) 
    public function escape($value) {
        return $this->conn->real_escape_string($value);
    }

    // ── Close the connection (usually not needed — PHP closes it on script end) 
    public function close() {
        if ($this->conn) {
            $this->conn->close();
            self::$instance = null;
        }
    }

    // ── Prevent cloning of the singleton ─────────────────────────────────────
    private function __clone() {}
}
?>
