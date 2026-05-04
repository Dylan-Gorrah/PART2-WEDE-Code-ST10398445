<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : viewItem.php
 * Description    : Detailed view for a single clothing item.
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

$itemID = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare(
    "SELECT c.clothesID, c.title, c.brand, c.size, c.price, c.description,
            c.category, c.itemCondition, c.status,
            u.firstName AS sellerFirst, u.lastName AS sellerLast
     FROM tblClothes c
     JOIN tblUser u ON c.userID = u.userID
     WHERE c.clothesID = ?"
);
$stmt->bind_param("i", $itemID);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

if (!$item) {
    header("Location: dashboard.php");
    exit();
}

// Helper: find local image for an item, or return placeholder
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
    return "https://placehold.co/800x600/e8e0d5/5c4033?text=" . urlencode($item['brand'] . ' ' . $item['title']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= htmlspecialchars($item['title']) ?> — Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="css/style.css"/>
    <style>
        .item-detail-wrap { padding-top: 24px; padding-bottom: 80px; }
        .item-img {
            width: 100%;
            aspect-ratio: 4 / 3;
            background: var(--stone-pale);
            border-radius: var(--radius-lg);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .item-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .item-badge-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 16px;
        }
        .item-title {
            font-family: var(--serif);
            font-size: 1.8rem;
            color: var(--espresso);
            margin-top: 12px;
        }
        .item-brand {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--gold);
        }
        .item-price {
            font-family: var(--serif);
            font-size: 2rem;
            color: var(--espresso);
            margin-top: 8px;
        }
        .item-meta {
            font-size: 14px;
            color: var(--stone);
            margin-top: 4px;
        }
        .item-desc {
            margin-top: 20px;
            line-height: 1.6;
            color: var(--espresso);
        }
        .seller-card {
            background: var(--ivory);
            border: 1px solid var(--ivory-dark);
            border-radius: var(--radius);
            padding: 16px;
            margin-top: 24px;
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

<div class="page-wrap">
<div class="container item-detail-wrap">

    <a href="dashboard.php" class="btn btn-outline btn-sm" style="margin-bottom:16px;">
        ← Back to Browse
    </a>

    <div class="item-img">
        <img src="<?= getItemImage($item) ?>"
             alt="<?= htmlspecialchars($item['title']) ?>"/>
    </div>

    <div class="item-badge-row">
        <span class="badge badge-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span>
        <span class="badge badge-verified"><?= htmlspecialchars($item['itemCondition'] ?? 'Good') ?></span>
        <span class="badge" style="background:var(--ivory-dark);color:var(--espresso);">
            <?= htmlspecialchars($item['category'] ?? 'Clothing') ?>
        </span>
    </div>

    <div class="item-brand"><?= htmlspecialchars($item['brand']) ?></div>
    <div class="item-title"><?= htmlspecialchars($item['title']) ?></div>
    <div class="item-price">R<?= number_format((float)$item['price'], 2) ?></div>
    <div class="item-meta">Size <?= htmlspecialchars($item['size']) ?> · Sold by <?= htmlspecialchars($item['sellerFirst'] . ' ' . $item['sellerLast']) ?></div>

    <div class="item-desc">
        <?= nl2br(htmlspecialchars($item['description'] ?? 'No description provided.')) ?>
    </div>

    <div class="seller-card">
        <div style="font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--stone);margin-bottom:4px;">Seller</div>
        <div style="font-family:var(--serif);font-size:1.1rem;color:var(--espresso);">
            <?= htmlspecialchars($item['sellerFirst'] . ' ' . $item['sellerLast']) ?>
        </div>
        <div style="font-size:12px;color:var(--stone);margin-top:2px;">Verified Pastimes member</div>
    </div>

    <?php if ($item['status'] === 'available'): ?>
    <form method="POST" action="addToCart.php" style="margin-top:24px;">
        <input type="hidden" name="clothesID" value="<?= $item['clothesID'] ?>"/>
        <button type="submit" class="btn btn-primary btn-full" style="height:52px;font-size:13px;letter-spacing:0.1em;">
            Add to Cart — R<?= number_format((float)$item['price'], 2) ?>
        </button>
    </form>
    <?php else: ?>
    <div class="alert alert-warning" style="margin-top:24px;">
        This item is no longer available.
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
    <a href="dashboard.php" class="bnav-item active">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        Browse
    </a>
    <a href="cart.php" class="bnav-item">
        <svg viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="16" y1="10" x2="16" y2="18"/><line x1="8" y1="10" x2="8" y2="18"/></svg>
        Cart
    </a>
</nav>

<script src="js/main.js"></script>
</body>
</html>
