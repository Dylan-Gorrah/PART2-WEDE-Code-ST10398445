<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : adminPanel.php
 * Description    : Main admin dashboard for Pastimes.
 *                  Shows site stats, lists ALL users, and allows the admin
 *                  to approve (verify) or reject pending registrations.
 *                  Admin CRUD for customers is handled in manageCustomers.php.
 */

session_start();
require_once 'DBConn.php';
require_once 'classes/AdminAuth.php';

// Instantiate AdminAuth OOP class and enforce admin login
$adminAuth = new AdminAuth($conn);
$adminAuth->requireLogin();  // Redirects to adminLogin.php if not logged in

// ── Handle quick-action POST (approve / reject right from this page) ─────────
$actionMsg   = '';
$actionType  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['userID'])) {
    $userID = (int) $_POST['userID'];
    $action = $_POST['action'];

    if ($action === 'verify') {
        if ($adminAuth->updateUserStatus($userID, 'verified')) {
            $actionMsg  = "User #$userID has been approved and can now log in.";
            $actionType = 'success';
        } else {
            $actionMsg  = "Could not approve user #$userID.";
            $actionType = 'error';
        }
    } elseif ($action === 'reject') {
        if ($adminAuth->updateUserStatus($userID, 'rejected')) {
            $actionMsg  = "User #$userID has been rejected.";
            $actionType = 'error';
        } else {
            $actionMsg  = "Could not reject user #$userID.";
            $actionType = 'error';
        }
    } elseif ($action === 'delete') {
        if ($adminAuth->deleteCustomer($userID)) {
            $actionMsg  = "User #$userID has been permanently deleted.";
            $actionType = 'success';
        } else {
            $actionMsg  = "Could not delete user #$userID.";
            $actionType = 'error';
        }
    }
}

// ── Load data for the page ───────────────────────────────────────────────────
$stats       = $adminAuth->getStats();
$pendingUsers = $adminAuth->getAllUsers('pending');
$allUsers    = $adminAuth->getAllUsers();

// ── Active page for sidebar nav ──────────────────────────────────────────────
$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Panel — Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body style="margin:0;padding:0;">

<!-- ── ADMIN TOP BAR ─────────────────────────────────────────────────────── -->
<div class="admin-topbar">
    <span class="logo">PASTIMES</span>
    <span style="color:rgba(250,246,239,0.5);font-size:11px;letter-spacing:0.1em;text-transform:uppercase;">Admin Panel</span>
    <span style="margin-left:auto;color:rgba(250,246,239,0.7);">
        <?= htmlspecialchars($_SESSION['adminName']) ?>
    </span>
    <a href="logout.php" style="color:var(--gold-light);font-size:12px;font-weight:600;letter-spacing:0.08em;text-decoration:none;">Sign Out</a>
</div>

<!-- ── TWO-COLUMN LAYOUT: SIDEBAR + CONTENT ─────────────────────────────── -->
<div class="admin-layout">

    <!-- SIDEBAR NAV -->
    <aside class="admin-sidebar">
        <a href="adminPanel.php" class="admin-nav-link active">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="manageCustomers.php" class="admin-nav-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Customers
            <?php if ($stats['pendingUsers'] > 0): ?>
                <span style="background:var(--orange);color:#fff;border-radius:20px;padding:1px 7px;font-size:9px;font-weight:700;margin-left:4px;">
                    <?= $stats['pendingUsers'] ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="manageCustomers.php?action=add" class="admin-nav-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Customer
        </a>
        <div style="height:1px;background:rgba(250,246,239,0.08);margin:12px 0;"></div>
        <a href="loadClothingStore.php" class="admin-nav-link" style="color:rgba(250,246,239,0.4);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.83"/></svg>
            Reset DB
        </a>
        <a href="login.php" class="admin-nav-link" style="color:rgba(250,246,239,0.4);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            View Store
        </a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="admin-content">

        <div class="admin-page-title">Dashboard</div>
        <div class="admin-page-sub">
            Welcome back, <strong><?= htmlspecialchars($_SESSION['adminName']) ?></strong> —
            here's what's happening on Pastimes today.
        </div>

        <!-- Action feedback message -->
        <?php if ($actionMsg): ?>
            <div class="alert alert-<?= $actionType === 'success' ? 'success' : 'error' ?> auto-dismiss">
                <?= htmlspecialchars($actionMsg) ?>
            </div>
        <?php endif; ?>

        <!-- ── STAT CARDS ─────────────────────────────────────────────────── -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-num"><?= $stats['totalUsers'] ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card" style="border-color:<?= $stats['pendingUsers'] > 0 ? 'var(--orange)' : 'var(--ivory-dark)' ?>;">
                <div class="stat-num" style="color:<?= $stats['pendingUsers'] > 0 ? 'var(--orange)' : 'inherit' ?>;">
                    <?= $stats['pendingUsers'] ?>
                </div>
                <div class="stat-label">Awaiting Approval</div>
            </div>
            <div class="stat-card">
                <div class="stat-num" style="color:var(--green);"><?= $stats['verifiedUsers'] ?></div>
                <div class="stat-label">Verified Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?= $stats['totalClothes'] ?></div>
                <div class="stat-label">Clothing Items</div>
            </div>
            <div class="stat-card">
                <div class="stat-num" style="color:var(--sage);"><?= $stats['availableItems'] ?></div>
                <div class="stat-label">Available Items</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?= $stats['totalOrders'] ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>

        <!-- ── PENDING APPROVALS ──────────────────────────────────────────── -->
        <div class="card mb-32">
            <div class="d-flex align-center justify-between mb-16">
                <div>
                    <div class="card-title">Pending Registrations</div>
                    <div class="card-subtitle">These users registered but need your approval before they can log in.</div>
                </div>
                <a href="manageCustomers.php" class="btn btn-outline btn-sm">View All Customers</a>
            </div>

            <?php if (empty($pendingUsers)): ?>
                <div class="alert alert-success">
                    No pending registrations — all users are processed. ✓
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Phone</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingUsers as $user): ?>
                            <tr data-status="pending">
                                <td><?= htmlspecialchars($user['userID']) ?></td>
                                <td><strong><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></strong></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['phone'] ?? '—') ?></td>
                                <td style="font-size:12px;color:var(--stone);">
                                    <?= date('d M Y', strtotime($user['createdAt'])) ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-8">
                                        <!-- APPROVE button -->
                                        <form method="POST" action="adminPanel.php" style="display:inline;">
                                            <input type="hidden" name="userID" value="<?= $user['userID'] ?>"/>
                                            <input type="hidden" name="action" value="verify"/>
                                            <button type="submit" class="btn btn-success btn-sm">
                                                ✓ Approve
                                            </button>
                                        </form>
                                        <!-- REJECT button -->
                                        <form method="POST" action="adminPanel.php" style="display:inline;">
                                            <input type="hidden" name="userID" value="<?= $user['userID'] ?>"/>
                                            <input type="hidden" name="action" value="reject"/>
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                data-confirm="Reject registration for <?= htmlspecialchars($user['firstName']) ?>?">
                                                ✗ Reject
                                            </button>
                                        </form>
                                        <!-- EDIT link -->
                                        <a href="manageCustomers.php?action=edit&id=<?= $user['userID'] ?>"
                                           class="btn btn-outline btn-sm">Edit</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── ALL USERS TABLE ───────────────────────────────────────────── -->
        <div class="card">
            <div class="d-flex align-center justify-between mb-16">
                <div>
                    <div class="card-title">All Registered Users</div>
                    <div class="card-subtitle">Complete user list from tblUser — click Edit to modify any record.</div>
                </div>
                <a href="manageCustomers.php?action=add" class="btn btn-primary btn-sm">+ Add Customer</a>
            </div>

            <?php if (empty($allUsers)): ?>
                <div class="alert alert-info">No users in the database yet. Run createTable.php to load seed data.</div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $user): ?>
                            <tr data-status="<?= htmlspecialchars($user['status']) ?>">
                                <td><?= htmlspecialchars($user['userID']) ?></td>
                                <td><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $user['status'] ?>">
                                        <?= htmlspecialchars($user['status']) ?>
                                    </span>
                                </td>
                                <td style="font-size:12px;color:var(--stone);">
                                    <?= date('d M Y', strtotime($user['createdAt'])) ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-8">
                                        <a href="manageCustomers.php?action=edit&id=<?= $user['userID'] ?>"
                                           class="btn btn-outline btn-sm">Edit</a>
                                        <form method="POST" action="adminPanel.php" style="display:inline;">
                                            <input type="hidden" name="userID" value="<?= $user['userID'] ?>"/>
                                            <input type="hidden" name="action" value="delete"/>
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                data-confirm="Permanently delete <?= htmlspecialchars($user['firstName']) ?>? This cannot be undone.">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div><!-- end .admin-layout -->

<script src="js/main.js"></script>
</body>
</html>
