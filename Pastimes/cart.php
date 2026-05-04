<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : cart.php
 * Description    : Shopping cart page — displays items added to cart, allows checkout.
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

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cartItems = [];
$total = 0;

// Fetch full item details for cart items
if (!empty($_SESSION['cart'])) {
    $ids = array_map('intval', $_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $stmt = $conn->prepare(
        "SELECT c.clothesID, c.title, c.brand, c.size, c.price, c.itemCondition,
                u.firstName AS sellerFirst, u.lastName AS sellerLast
         FROM tblClothes c
         JOIN tblUser u ON c.userID = u.userID
         WHERE c.clothesID IN ($placeholders) AND c.status = 'available'"
    );
    
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $total += $row['price'];
    }
    $stmt->close();
}

// Helper function
function getItemImage($item) {
    $id = $item['clothesID'];
    $title = strtolower($item['title']);
    $kebab = preg_replace('/[^a-z0-9]+/', '-', $title);
    $kebab = trim($kebab, '-');

    $patterns = [
        "images/item-" . sprintf('%02d', $id) . "-{$kebab}.jpg",
        "images/item-" . sprintf('%02d', $id) . "-{$kebab}.jpeg",
        "images/item-" . sprintf('%02d', $id) . "-{$kebab}.png",
        "images/item-{$id}-{$kebab}.jpg",
        "images/item-{$id}-{$kebab}.jpeg",
        "images/item-{$id}-{$kebab}.png",
    ];

    foreach ($patterns as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    return "https://placehold.co/400x500/e8e0d5/5c4033?text=" . urlencode($item['brand']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Your Cart — Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="css/style.css"/>
    <style>
        .cart-page { padding-top: 24px; padding-bottom: 100px; }
        .cart-header {
            margin-bottom: 24px;
        }
        .cart-title {
            font-family: var(--serif);
            font-size: 1.8rem;
            font-weight: 400;
            margin-bottom: 4px;
        }
        .cart-count {
            font-size: 13px;
            color: var(--stone);
            letter-spacing: 0.05em;
        }
        .cart-item {
            display: flex;
            gap: 14px;
            padding: 18px 0;
            border-bottom: 1px solid var(--ivory-dark);
        }
        .cart-item-img {
            width: 100px;
            height: 120px;
            object-fit: cover;
            border-radius: var(--radius);
            flex-shrink: 0;
        }
        .cart-item-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .cart-item-brand {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 4px;
        }
        .cart-item-name {
            font-family: var(--serif);
            font-size: 1.1rem;
            color: var(--espresso);
            margin-bottom: 4px;
        }
        .cart-item-meta {
            font-size: 12px;
            color: var(--stone);
            margin-bottom: 8px;
        }
        .cart-item-price {
            font-family: var(--serif);
            font-size: 1.2rem;
            color: var(--espresso);
            font-weight: 500;
            margin-top: auto;
        }
        .cart-item-remove {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--stone);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            margin-top: 8px;
            align-self: flex-start;
        }
        .cart-item-remove:hover {
            color: var(--red);
        }
        .order-summary {
            background: var(--cream-card);
            border: 1px solid var(--ivory-dark);
            border-radius: var(--radius-lg);
            padding: 20px;
            margin-top: 24px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        .summary-row.total {
            font-family: var(--serif);
            font-size: 1.3rem;
            font-weight: 500;
            border-top: 1px solid var(--ivory-dark);
            margin-top: 8px;
            padding-top: 16px;
        }
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: var(--stone);
        }
        .empty-cart svg {
            width: 64px;
            height: 64px;
            stroke: var(--stone-pale);
            margin-bottom: 16px;
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
<div class="page-wrap">
<div class="container cart-page">

    <a href="dashboard.php" class="btn btn-outline btn-sm" style="margin-bottom:16px;">
        ← Continue Browsing
    </a>

    <div class="cart-header">
        <h1 class="cart-title">Your Cart</h1>
        <p class="cart-count"><?= count($cartItems) ?> item<?= count($cartItems) !== 1 ? 's' : '' ?></p>
    </div>

    <?php if (empty($cartItems)): ?>
    
        <div class="empty-cart">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="16" y1="10" x2="16" y2="18"/>
                <line x1="8" y1="10" x2="8" y2="18"/>
            </svg>
            <p style="font-size:1.1rem;margin-bottom:8px;">Your cart is empty</p>
            <p style="font-size:13px;margin-bottom:20px;">Browse our pre-loved collection and add items you love.</p>
            <a href="dashboard.php" class="btn btn-primary">Start Browsing</a>
        </div>
    
    <?php else: ?>
    
        <?php foreach ($cartItems as $item): ?>
        <div class="cart-item">
            <img src="<?= getItemImage($item) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="cart-item-img"/>
            <div class="cart-item-body">
                <div class="cart-item-brand"><?= htmlspecialchars($item['brand']) ?></div>
                <div class="cart-item-name"><?= htmlspecialchars($item['title']) ?></div>
                <div class="cart-item-meta">
                    Size <?= htmlspecialchars($item['size']) ?> · <?= htmlspecialchars($item['itemCondition'] ?? 'Good') ?>
                </div>
                <div class="cart-item-price">R<?= number_format((float)$item['price'], 2) ?></div>
                <form method="POST" action="removeFromCart.php" style="display:inline;">
                    <input type="hidden" name="clothesID" value="<?= $item['clothesID'] ?>"/>
                    <button type="submit" class="cart-item-remove">Remove</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="order-summary">
            <div class="summary-row">
                <span>Subtotal</span>
                <span>R<?= number_format($total, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Delivery</span>
                <span style="color:var(--sage);">Free</span>
            </div>
            <div class="summary-row total">
                <span>Total</span>
                <span>R<?= number_format($total, 2) ?></span>
            </div>
            
            <form method="POST" action="checkout.php" style="margin-top:20px;">
                <button type="submit" class="btn btn-primary btn-full">
                    Confirm & Pay — R<?= number_format($total, 2) ?>
                </button>
            </form>
            
            <p style="font-size:11px;color:var(--stone);text-align:center;margin-top:12px;">
                By confirming, you agree to purchase these items from the sellers.
            </p>
        </div>

    <?php endif; ?>

</div>
</div>

<!-- ── BOTTOM NAVIGATION ─────────────────────────────────────────────────── -->
<nav class="bottom-nav">
    <a href="dashboard.php" class="bnav-item">
        <svg viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Home
    </a>
    <a href="dashboard.php" class="bnav-item">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        Browse
    </a>
    <a href="cart.php" class="bnav-item active">
        <svg viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="16" y1="10" x2="16" y2="18"/><line x1="8" y1="10" x2="8" y2="18"/></svg>
        Cart
    </a>
</nav>

<script src="js/main.js"></script>
</body>
</html>
