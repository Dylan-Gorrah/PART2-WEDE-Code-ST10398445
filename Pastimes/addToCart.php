<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : addToCart.php
 * Description    : Adds an item to the shopping cart (stored in session).
 */

session_start();
require_once 'DBConn.php';
require_once 'classes/UserAuth.php';

$auth = new UserAuth($conn);
$auth->requireLogin();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clothesID = isset($_POST['clothesID']) ? intval($_POST['clothesID']) : 0;
    
    if ($clothesID > 0) {
        // Check if item exists and is available
        $stmt = $conn->prepare("SELECT clothesID, status FROM tblClothes WHERE clothesID = ?");
        $stmt->bind_param("i", $clothesID);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();
        
        if ($item && $item['status'] === 'available') {
            // Add to cart if not already there
            if (!in_array($clothesID, $_SESSION['cart'])) {
                $_SESSION['cart'][] = $clothesID;
                $_SESSION['cart_message'] = "Item added to cart!";
            } else {
                $_SESSION['cart_message'] = "Item is already in your cart.";
            }
        } else {
            $_SESSION['cart_error'] = "Sorry, this item is no longer available.";
        }
    }
    
    // Redirect back to referring page or dashboard
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'dashboard.php';
    header("Location: $redirect");
    exit();
}

// If accessed directly without POST, redirect to dashboard
header("Location: dashboard.php");
exit();
?>
