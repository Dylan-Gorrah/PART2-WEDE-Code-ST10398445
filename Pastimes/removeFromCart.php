<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : removeFromCart.php
 * Description    : Removes an item from the shopping cart.
 */

session_start();
require_once 'DBConn.php';
require_once 'classes/UserAuth.php';

$auth = new UserAuth($conn);
$auth->requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clothesID = isset($_POST['clothesID']) ? intval($_POST['clothesID']) : 0;
    
    if ($clothesID > 0 && isset($_SESSION['cart'])) {
        // Remove item from cart array
        $key = array_search($clothesID, $_SESSION['cart']);
        if ($key !== false) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
            $_SESSION['cart_message'] = "Item removed from cart.";
        }
    }
}

// Redirect back to cart
header("Location: cart.php");
exit();
?>
