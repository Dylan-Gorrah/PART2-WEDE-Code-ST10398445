<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : dashboard.php
 * Description    : Main dashboard for a logged-in Pastimes buyer.
 *                  Shows welcome banner with "User [Name] is logged in",
 *                  their account summary, and available clothing listings.
 */

session_start();
require_once 'DBConn.php';
require_once 'classes/UserAuth.php';

// Instantiate OOP class and enforce login
$auth = new UserAuth($conn);
$auth->requireLogin();

// Fetch fresh user data from DB using the session userID
$user = $auth->getUserById($_SESSION['userID']);

// If DB was reset (e.g. loadClothingStore.php), the old userID may no longer exist
if (!$user) {
    session_destroy();
    header("Location: login.php?reason=session_expired");
    exit();
}

// ── Fetch available clothing items for the buyer to browse ───────────────────
$clothesResult = $conn->query(
    "SELECT c.clothesID, c.title, c.brand, c.size, c.price, c.description, c.status,
            u.firstName AS sellerFirst, u.lastName AS sellerLast
     FROM tblClothes c
     JOIN tblUser u ON c.userID = u.userID
     WHERE c.status = 'available'
     ORDER BY c.clothesID DESC
     LIMIT 12"
);
$clothesItems = [];
if ($clothesResult) {
    while ($row = $clothesResult->fetch_assoc()) {
        $clothesItems[] = $row;
    }
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get cart messages
$cartMessage = $_SESSION['cart_message'] ?? '';
$cartError = $_SESSION['cart_error'] ?? '';
unset($_SESSION['cart_message'], $_SESSION['cart_error']);

// Helper: find local image for an item, or return placeholder
function getItemImage($item) {
    $id = $item['clothesID'];
    $title = strtolower($item['title']);
    // Create kebab-case filename pattern
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
    // Fallback to placeholder with brand name
    return "https://placehold.co/400x300/e8e0d5/5c4033?text=" . urlencode($item['brand']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Dashboard — Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="css/style.css"/>
    <style>
        /* Product grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .product-card {
            background: var(--cream-card);
            border: 1px solid var(--ivory-dark);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        .product-img {
            width: 100%;
            height: 200px;
            background: var(--stone-pale);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--stone);
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.06em;
        }
        .product-body {
            padding: 16px;
        }
        .product-brand {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 4px;
        }
        .product-title {
            font-family: var(--serif);
            font-size: 1.1rem;
            color: var(--espresso);
            margin-bottom: 4px;
        }
        .product-meta {
            font-size: 12px;
            color: var(--stone);
            margin-bottom: 12px;
        }
        .product-price {
            font-family: var(--serif);
            font-size: 1.3rem;
            color: var(--espresso);
            font-weight: 500;
        }
        /* Quick info grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            margin-top: 20px;
        }
        .info-tile {
            background: var(--ivory-dark);
            border-radius: var(--radius);
            padding: 16px 18px;
        }
        .info-tile-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--stone);
            margin-bottom: 4px;
        }
        .info-tile-value {
            font-family: var(--serif);
            font-size: 1.1rem;
            color: var(--espresso);
        }
    </style>
</head>
<body>

<!-- ── TOP NAV ─────────────────────────────────────────────────────────────── -->
<nav class="top-nav">
    <a href="index.php" class="nav-logo">PASTIMES</a>

    <!-- Nav user pill — shows logged in user name -->
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
    <a href="logout.php" class="nav-icon-btn" title="Sign Out">
        <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </a>
</nav>

<!-- ── MAIN CONTENT ──────────────────────────────────────────────────────────── -->
<div class="page-wrap">
<div class="container" style="padding-top:32px;padding-bottom:60px;">

    <!-- ══════════════════════════════════════════════════════════════════════
         WELCOME BANNER — "User [Name] is logged in" (required by brief)
    ══════════════════════════════════════════════════════════════════════ -->
    <div class="welcome-banner">
        <div class="welcome-name">
            User <?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?> is logged in
        </div>
        <div class="welcome-sub">
            Welcome back to Pastimes — browse pre-loved fashion below.
        </div>
    </div>

    <!-- Cart flash messages -->
    <?php if ($cartMessage): ?>
        <div class="flash-message flash-success"><?= htmlspecialchars($cartMessage) ?></div>
    <?php endif; ?>
    <?php if ($cartError): ?>
        <div class="flash-message flash-error"><?= htmlspecialchars($cartError) ?></div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════════════════════
         CLOTHING LISTINGS — what's available to buy
    ══════════════════════════════════════════════════════════════════════ -->
    <div style="margin-bottom:16px;">
        <h2 style="font-family:var(--serif);font-size:1.8rem;font-weight:400;margin-bottom:4px;">
            Available Now
        </h2>
        <p style="font-size:13px;color:var(--stone);">
            Pre-loved branded clothing — all items verified by Pastimes.
        </p>
    </div>

    <!-- Category chip filter strip -->
    <div style="overflow-x:auto; -webkit-overflow-scrolling:touch; padding: 0 0 12px;">
        <div style="display:flex; gap:8px; width:max-content;">
            <button class="cat-chip active" onclick="filterCat(this,'all')">All</button>
            <button class="cat-chip" onclick="filterCat(this,'tops')">Tops</button>
            <button class="cat-chip" onclick="filterCat(this,'bottoms')">Bottoms</button>
            <button class="cat-chip" onclick="filterCat(this,'dresses')">Dresses</button>
            <button class="cat-chip" onclick="filterCat(this,'outerwear')">Outerwear</button>
            <button class="cat-chip" onclick="filterCat(this,'shoes')">Shoes</button>
            <button class="cat-chip" onclick="filterCat(this,'accessories')">Accessories</button>
        </div>
    </div>

    <?php if (empty($clothesItems)): ?>
        <div class="alert alert-info">
            No clothing items listed yet. Run <code>loadClothingStore.php</code> to load sample data, or ask an admin to add listings.
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($clothesItems as $item):
                // Derive a dummy category from the title for demo filtering
                $titleLower = strtolower($item['title']);
                if (str_contains($titleLower, 'dress')) $cat = 'dresses';
                elseif (str_contains($titleLower, 'jean') || str_contains($titleLower, 'skirt') || str_contains($titleLower, 'short')) $cat = 'bottoms';
                elseif (str_contains($titleLower, 'jacket') || str_contains($titleLower, 'coat')) $cat = 'outerwear';
                elseif (str_contains($titleLower, 'shoe') || str_contains($titleLower, 'sneaker') || str_contains($titleLower, 'boot')) $cat = 'shoes';
                elseif (str_contains($titleLower, 'bag') || str_contains($titleLower, 'hat') || str_contains($titleLower, 'scarf')) $cat = 'accessories';
                else $cat = 'tops';
            ?>
            <div class="product-card" data-category="<?= $cat ?>">
                <!-- Product image area with wishlist + condition badge -->
                <div class="product-img" style="position:relative;">
                    <img src="<?= getItemImage($item) ?>"
                         alt="<?= htmlspecialchars($item['title']) ?>"
                         style="width:100%;height:100%;object-fit:cover;position:absolute;top:0;left:0;"/>
                    <button class="wishlist-btn" data-id="<?= $item['clothesID'] ?>">
                        <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </button>
                    <span class="condition-badge">Good</span>
                </div>

                <div class="product-body">
                    <div class="product-brand"><?= htmlspecialchars($item['brand']) ?></div>
                    <div class="product-title"><?= htmlspecialchars($item['title']) ?></div>
                    <div class="product-meta">
                        Size <?= htmlspecialchars($item['size']) ?> ·
                        Sold by <?= htmlspecialchars($item['sellerFirst'] . ' ' . $item['sellerLast']) ?>
                    </div>

                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;">
                        <div class="product-price">
                            R<?= number_format((float)$item['price'], 2) ?>
                        </div>
                        <span class="badge badge-available">Available</span>
                    </div>

                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <a href="viewItem.php?id=<?= $item['clothesID'] ?>"
                           class="btn btn-outline btn-sm"
                           style="flex:1;font-size:10px;">
                            View
                        </a>
                        <form method="POST" action="addToCart.php" style="flex:2;">
                            <input type="hidden" name="clothesID" value="<?= $item['clothesID'] ?>"/>
                            <button type="submit" class="btn btn-primary btn-full btn-sm" style="font-size:10px;">
                                Add to Cart — R<?= number_format((float)$item['price'], 0) ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
</div><!-- end page-wrap -->

<!-- ── BOTTOM NAVIGATION ─────────────────────────────────────────────────── -->
<nav class="bottom-nav">
    <a href="dashboard.php" class="bnav-item active">
        <svg viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Home
    </a>
    <a href="dashboard.php" class="bnav-item">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        Browse
    </a>
    <a href="cart.php" class="bnav-item">
        <svg viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="16" y1="10" x2="16" y2="18"/><line x1="8" y1="10" x2="8" y2="18"/></svg>
        Cart <?= count($_SESSION['cart']) > 0 ? '<span class="cart-badge">' . count($_SESSION['cart']) . '</span>' : '' ?>
    </a>
</nav>

<script src="js/main.js"></script>
<style>
    @media (max-width:640px) {
        .summary-cols { grid-template-columns: 1fr !important; }
    }
</style>
</body>
</html>
