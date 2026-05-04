<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : index.php
 * Description    : Landing page for Pastimes. Checks if a user is already
 *                  logged in and redirects appropriately.
 */
session_start();

// If user is already logged in, send them to the dashboard
if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
    header("Location: dashboard.php");
    exit();
}

// If admin is logged in, send to admin panel
if (isset($_SESSION['adminLoggedIn']) && $_SESSION['adminLoggedIn'] === true) {
    header("Location: adminPanel.php");
    exit();
}

// Otherwise, send to the login page
header("Location: login.php");
exit();
?>
