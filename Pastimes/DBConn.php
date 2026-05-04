<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : DBConn.php
 * Description    : MySQLi database connection for the ClothingStore database.
 *                  Include this file at the top of any PHP page that needs DB access.
 */

// ── Database credentials ──────────────────────────────────────────────────────
define('DB_HOST', 'localhost');  // Your MySQL host (localhost for XAMPP/WAMP)
define('DB_USER', 'root');       // MySQL username (default is 'root' for XAMPP)
define('DB_PASS', '');           // MySQL password (blank by default on XAMPP)
define('DB_NAME', 'ClothingStore'); // The database name as per the brief

// ── Create the MySQLi connection ──────────────────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// ── Stop everything if the connection fails ───────────────────────────────────
if ($conn->connect_error) {
    // In production you'd log this and show a user-friendly page.
    // For the PoE, showing the error is fine so the lecturer can debug.
    die('<div style="font-family:sans-serif;padding:20px;background:#fee;border:1px solid #c00;border-radius:6px;">'
      . '<strong>Database Connection Failed:</strong> '
      . htmlspecialchars($conn->connect_error)
      . '<br><small>Check DBConn.php — make sure ClothingStore database exists in phpMyAdmin.</small>'
      . '</div>');
}

// ── Force UTF-8 so South African characters display correctly ─────────────────
$conn->set_charset("utf8");
?>
