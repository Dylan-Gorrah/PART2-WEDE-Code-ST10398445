<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : orderSuccess.php
 * Description    : Order confirmation page shown after successful checkout.
 */

session_start();
require_once 'DBConn.php';
require_once 'classes/UserAuth.php';

$auth = new UserAuth($conn);
$auth->requireLogin();

// Check if user just completed checkout
if (!isset($_SESSION['checkout_success'])) {
    header("Location: dashboard.php");
    exit();
}

$orderCount = $_SESSION['order_count'] ?? 0;
$orderTotal = $_SESSION['order_total'] ?? 0;

// Clear checkout session data
unset($_SESSION['checkout_success']);
unset($_SESSION['order_count']);
unset($_SESSION['order_total']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Order Confirmed — Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="css/style.css"/>
    <style>
        .success-page {
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .success-card {
            background: var(--cream-card);
            border: 1px solid var(--ivory-dark);
            border-radius: var(--radius-lg);
            padding: 48px 40px;
            text-align: center;
            max-width: 420px;
            width: 100%;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: var(--green-pale);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .success-icon svg {
            width: 40px;
            height: 40px;
            stroke: var(--green);
            stroke-width: 2;
        }
        .success-title {
            font-family: var(--serif);
            font-size: 1.8rem;
            font-weight: 400;
            color: var(--espresso);
            margin-bottom: 12px;
        }
        .success-message {
            font-size: 15px;
            color: var(--stone);
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .order-summary {
            background: var(--ivory);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 24px;
            text-align: left;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .summary-row.total {
            font-family: var(--serif);
            font-size: 1.1rem;
            font-weight: 500;
            border-top: 1px solid var(--ivory-dark);
            margin-top: 12px;
            padding-top: 12px;
        }
        .next-steps {
            font-size: 13px;
            color: var(--stone);
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--ivory-dark);
        }
    </style>
</head>
<body>

<!-- ── TOP NAV ─────────────────────────────────────────────────────────────── -->
<nav class="top-nav">
    <a href="dashboard.php" class="nav-logo">PASTIMES</a>
    <div class="nav-user-pill">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
        </svg>
        <?= htmlspecialchars($_SESSION['name']) ?>
    </div>
    <a href="settings.php" class="nav-icon-btn" title="Settings">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15 1.65 1.65 0 0 0 3 15v-.09a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33h.09A1.65 1.65 0 0 0 9 4.6V4.5a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 .89 1.65 1.65 0 0 0 1.51-.1l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06A1.65 1.65 0 0 0 15 15Z"/>
        </svg>
    </a>
</nav>

<!-- ── MAIN CONTENT ──────────────────────────────────────────────────────────── -->
<div class="success-page">
    <div class="success-card">
        <div class="success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        
        <h1 class="success-title">Order Confirmed!</h1>
        <p class="success-message">
            Thank you for your purchase. Your order has been placed and the sellers will be notified to ship your items.
        </p>
        
        <div class="order-summary">
            <div class="summary-row">
                <span>Items ordered</span>
                <span><?= $orderCount ?></span>
            </div>
            <div class="summary-row">
                <span>Delivery</span>
                <span style="color:var(--sage);">Free</span>
            </div>
            <div class="summary-row total">
                <span>Total paid</span>
                <span>R<?= number_format($orderTotal, 2) ?></span>
            </div>
        </div>
        
        <div style="display:flex;gap:10px;justify-content:center;">
            <a href="dashboard.php" class="btn btn-primary">Continue Shopping</a>
            <a href="settings.php" class="btn btn-outline">My Account</a>
        </div>
        
        <div class="next-steps">
            <strong>What happens next?</strong><br>
            Sellers will package and ship your items within 2–3 business days. You'll receive tracking information via email.
        </div>
    </div>
</div>

<script src="js/main.js"></script>
</body>
</html>
