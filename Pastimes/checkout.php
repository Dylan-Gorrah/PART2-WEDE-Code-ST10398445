<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : checkout.php
 * Description    : Processes cart items and creates orders in tblAorder.
 */

session_start();
require_once 'DBConn.php';
require_once 'classes/UserAuth.php';

$auth = new UserAuth($conn);
$auth->requireLogin();

$user = $auth->getUserById($_SESSION['userID']);
if (!$user) {
    session_destroy();
    header("Location: login.php?reason=session_expired");
    exit();
}

// Check if cart has items
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$buyerID = $_SESSION['userID'];
$cartItems = [];
$total = 0;

// Fetch cart item details and validate they're still available
$ids = array_map('intval', $_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $conn->prepare(
    "SELECT clothesID, title, brand, price, status 
     FROM tblClothes 
     WHERE clothesID IN ($placeholders)"
);
$stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'available') {
        $cartItems[] = $row;
        $total += $row['price'];
    }
}
$stmt->close();

if (empty($cartItems)) {
    $_SESSION['cart_error'] = "Sorry, the items in your cart are no longer available.";
    header("Location: cart.php");
    exit();
}

// Get delivery address
$deliveryAddress = $user['deliveryAddress'] ?? 'No delivery address provided';

// Insert orders into tblAorder
$successCount = 0;
$orderIDs = [];

$stmt = $conn->prepare(
    "INSERT INTO tblAorder (buyerID, clothesID, quantity, totalPrice, status, deliveryAddress) 
     VALUES (?, ?, 1, ?, 'pending', ?)"
);

foreach ($cartItems as $item) {
    $stmt->bind_param("iids", $buyerID, $item['clothesID'], $item['price'], $deliveryAddress);
    if ($stmt->execute()) {
        $successCount++;
        $orderIDs[] = $stmt->insert_id;
        
        // Mark item as sold
        $updateStmt = $conn->prepare("UPDATE tblClothes SET status = 'sold' WHERE clothesID = ?");
        $updateStmt->bind_param("i", $item['clothesID']);
        $updateStmt->execute();
        $updateStmt->close();
    }
}
$stmt->close();

// Clear cart after successful checkout
$_SESSION['cart'] = [];
$_SESSION['checkout_success'] = true;
$_SESSION['order_count'] = $successCount;
$_SESSION['order_total'] = $total;

// Redirect to success page
header("Location: orderSuccess.php");
exit();
?>
