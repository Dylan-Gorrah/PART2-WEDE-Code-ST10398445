<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : settings.php
 * Description    : User settings page — account details, delivery address, sign out.
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Settings — Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="css/style.css"/>
    <style>
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
        .page-wrap { padding-bottom: 80px; }
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
    <a href="logout.php" class="nav-icon-btn" title="Sign Out">
        <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </a>
</nav>

<!-- ── MAIN CONTENT ──────────────────────────────────────────────────────────── -->
<div class="page-wrap">
<div class="container" style="padding-top:32px;padding-bottom:60px;">

    <!-- Welcome banner -->
    <div class="welcome-banner">
        <div class="welcome-name">
            User <?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?> is logged in
        </div>
        <div class="welcome-sub">
            Welcome back to Pastimes — browse pre-loved fashion below.
        </div>
    </div>

    <!-- ── TWO COLUMN: Account summary + Quick links ─────────────────────── -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:32px;" class="summary-cols">

        <!-- Account details card -->
        <div class="card">
            <div class="card-title">My Account</div>
            <div class="card-subtitle">Your details from the database</div>
            <div class="info-grid">
                <div class="info-tile">
                    <div class="info-tile-label">Username</div>
                    <div class="info-tile-value"><?= htmlspecialchars($user['username']) ?></div>
                </div>
                <div class="info-tile">
                    <div class="info-tile-label">Email</div>
                    <div class="info-tile-value" style="font-size:0.9rem;word-break:break-all;">
                        <?= htmlspecialchars($user['email']) ?>
                    </div>
                </div>
                <div class="info-tile">
                    <div class="info-tile-label">Phone</div>
                    <div class="info-tile-value"><?= htmlspecialchars($user['phone'] ?? '—') ?></div>
                </div>
                <div class="info-tile">
                    <div class="info-tile-label">Status</div>
                    <div class="info-tile-value">
                        <span class="badge badge-<?= $user['status'] ?>"><?= $user['status'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Delivery address status -->
            <div style="margin-top:16px;padding:14px;background:var(--ivory);border-radius:var(--radius);border:1px solid var(--ivory-dark);">
                <div style="font-size:10px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--stone);margin-bottom:4px;">
                    Delivery Address
                </div>
                <?php if (!empty($user['deliveryAddress'])): ?>
                    <div style="font-size:14px;color:var(--espresso);">
                        <?= nl2br(htmlspecialchars($user['deliveryAddress'])) ?>
                    </div>
                    <a href="delivery.php" style="font-size:12px;color:var(--gold);margin-top:6px;display:inline-block;">
                        Edit address →
                    </a>
                <?php else: ?>
                    <div style="font-size:13px;color:var(--stone);">No delivery address saved yet.</div>
                    <a href="delivery.php" class="btn btn-outline btn-sm" style="margin-top:10px;">
                        + Add Delivery Address
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick actions card -->
        <div class="card">
            <div class="card-title">Quick Actions</div>
            <div class="card-subtitle">What would you like to do?</div>
            <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px;">
                <a href="delivery.php" class="btn btn-outline w-full" style="justify-content:flex-start;gap:12px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Update Delivery Address
                </a>
                <a href="login.php" class="btn btn-outline w-full" style="justify-content:flex-start;gap:12px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    View Login Details
                </a>
                <a href="logout.php" class="btn btn-outline w-full" style="justify-content:flex-start;gap:12px;color:var(--red);border-color:var(--red);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Sign Out
                </a>
            </div>

            <!-- Member since -->
            <div style="margin-top:auto;padding-top:20px;border-top:1px solid var(--ivory-dark);
                        font-size:12px;color:var(--stone);margin-top:24px;">
                Member since <?= date('F Y', strtotime($user['createdAt'])) ?>
            </div>
        </div>
    </div>

</div>
</div><!-- end page-wrap -->

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
    <a href="settings.php" class="bnav-item active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15 1.65 1.65 0 0 0 3 15v-.09a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33h.09A1.65 1.65 0 0 0 9 4.6V4.5a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 .89 1.65 1.65 0 0 0 1.51-.1l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06A1.65 1.65 0 0 0 15 15Z"/>
        </svg>
        Settings
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
