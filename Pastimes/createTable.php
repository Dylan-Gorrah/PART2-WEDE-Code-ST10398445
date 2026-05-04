<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : createTable.php
 * Description    : Drops tblUser if it exists, recreates it, then loads
 *                  seed data from database/userData.txt.
 *                  Run by visiting: http://localhost/pastimes/createTable.php
 *
 * Reference      : PHP password_hash() — https://www.php.net/manual/en/function.password-hash.php
 *                  MySQLi prepared statements — https://www.php.net/manual/en/mysqli.prepare.php
 */

// Include the database connection (DBConn.php must exist at root level)
require_once 'DBConn.php';

// ── Collect feedback to display at the end ───────────────────────────────────
$log = [];

// ════════════════════════════════════════════════════════════════════════════
//  STEP 1 — Drop tblUser if it already exists
//  We do this so running the script twice doesn't cause errors
// ════════════════════════════════════════════════════════════════════════════

// First, drop foreign key constraints in dependent tables to avoid errors
$conn->query("ALTER TABLE tblAorder DROP FOREIGN KEY IF EXISTS fk_order_buyer");
$conn->query("ALTER TABLE tblClothes DROP FOREIGN KEY IF EXISTS fk_clothes_user");

if ($conn->query("DROP TABLE IF EXISTS tblUser")) {
    $log[] = "✅ tblUser dropped (or didn't exist yet) — clean slate.";
} else {
    $log[] = "❌ Could not drop tblUser: " . $conn->error;
}

// ════════════════════════════════════════════════════════════════════════════
//  STEP 2 — (Re)create tblUser with all required columns
// ════════════════════════════════════════════════════════════════════════════
$createSQL = "
    CREATE TABLE IF NOT EXISTS tblUser (
        userID          INT             NOT NULL AUTO_INCREMENT,
        firstName       VARCHAR(100)    NOT NULL,
        lastName        VARCHAR(100)    NOT NULL,
        email           VARCHAR(255)    NOT NULL UNIQUE,
        username        VARCHAR(100)    NOT NULL UNIQUE,
        password        VARCHAR(255)    NOT NULL,
        phone           VARCHAR(20)     DEFAULT NULL,
        status          ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
        deliveryAddress TEXT            DEFAULT NULL,
        createdAt       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (userID)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

if ($conn->query($createSQL)) {
    $log[] = "✅ tblUser created successfully.";
} else {
    $log[] = "❌ Could not create tblUser: " . $conn->error;
    // No point continuing if the table wasn't created
    displayLog($log);
    exit();
}

// ════════════════════════════════════════════════════════════════════════════
//  STEP 3 — Read userData.txt and insert each row
//  Format: firstName|lastName|email|username|password|phone
// ════════════════════════════════════════════════════════════════════════════
$txtFile = __DIR__ . '/database/userData.txt';

if (!file_exists($txtFile)) {
    $log[] = "❌ userData.txt not found at: $txtFile";
    displayLog($log);
    exit();
}

$lines       = file($txtFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$inserted    = 0;
$skipped     = 0;

foreach ($lines as $lineNum => $line) {
    // Skip comment lines that start with #
    if (str_starts_with(trim($line), '#')) {
        continue;
    }

    $fields = explode('|', $line);

    // We expect exactly 6 fields: firstName|lastName|email|username|password|phone
    if (count($fields) < 6) {
        $log[]  = "⚠️  Line " . ($lineNum + 1) . " skipped — wrong number of fields (expected 6, got " . count($fields) . ")";
        $skipped++;
        continue;
    }

    // Trim whitespace from each field
    [$firstName, $lastName, $email, $username, $rawPassword, $phone] = array_map('trim', $fields);

    // Hash the plain text password from the file — never store plain text
    $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

    // Use a prepared statement to safely insert the data
    $stmt = $conn->prepare(
        "INSERT INTO tblUser (firstName, lastName, email, username, password, phone, status)
         VALUES (?, ?, ?, ?, ?, ?, 'verified')"
        // Seed users are set to 'verified' so you can test login immediately
    );
    $stmt->bind_param("ssssss", $firstName, $lastName, $email, $username, $hashedPassword, $phone);

    if ($stmt->execute()) {
        $log[] = "✅ Inserted: $firstName $lastName ($username)";
        $inserted++;
    } else {
        $log[] = "❌ Failed to insert $username: " . $stmt->error;
        $skipped++;
    }

    $stmt->close();
}

$log[] = "──────────────────────────────────────";
$log[] = "Done. Inserted: $inserted | Skipped/Failed: $skipped";

// ════════════════════════════════════════════════════════════════════════════
//  STEP 4 — Display all results in a readable page
// ════════════════════════════════════════════════════════════════════════════
displayLog($log);

// ── Helper function to render the output page ─────────────────────────────
function displayLog(array $messages) {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>createTable.php — Pastimes</title>
    <style>
        body { font-family: "Jost", system-ui, sans-serif; background: #FAF6EF; color: #1C130B; padding: 40px; }
        h1   { font-size: 1.4rem; margin-bottom: 20px; }
        .log { background: #1C130B; color: #F2E0B6; border-radius: 8px; padding: 24px; font-family: monospace; font-size: 14px; line-height: 1.8; }
        .log p { margin: 0; }
        a    { display: inline-block; margin-top: 20px; color: #BF8B30; }
    </style>
</head>
<body>
    <h1>createTable.php — tblUser reset log</h1>
    <div class="log">';
    foreach ($messages as $msg) {
        echo '<p>' . htmlspecialchars($msg) . '</p>';
    }
    echo '</div>
    <a href="login.php">← Go to login page</a>
</body>
</html>';
}
?>
