<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : logout.php
 * Description    : Destroys the active session (user or admin) and
 *                  redirects back to the login page.
 */

session_start();

// Determine where to redirect after logout
$redirectTo = 'login.php';
if (isset($_SESSION['adminLoggedIn'])) {
    $redirectTo = 'adminLogin.php';
}

// Clear all session data
$_SESSION = [];

// Expire the session cookie immediately
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to appropriate login page
header("Location: " . $redirectTo . "?msg=logged_out");
exit();
?>
