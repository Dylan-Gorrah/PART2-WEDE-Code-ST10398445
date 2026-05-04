<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : adminLogin.php
 * Description    : Administrator login page for Pastimes.
 *                  Checks credentials against tblAdmin.
 *                  On success: sets admin session and redirects to adminPanel.php.
 */

session_start();

// If already logged in as admin, skip to the panel
if (isset($_SESSION['adminLoggedIn']) && $_SESSION['adminLoggedIn'] === true) {
    header("Location: adminPanel.php");
    exit();
}

require_once 'DBConn.php';
require_once 'classes/AdminAuth.php';

$auth = new AdminAuth($conn);

$stickyUsername = '';
$errorMsg = '';
$reason = $_GET['reason'] ?? '';

// ════════════════════════════════════════════════════════════════════════════
//  Handle POST
// ════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stickyUsername = htmlspecialchars(trim($_POST['username'] ?? ''));
    $rawPassword    = $_POST['password'] ?? '';

    $result = $auth->login($stickyUsername, $rawPassword);

    if ($result['success']) {
        header("Location: adminPanel.php");
        exit();
    } else {
        $errorMsg = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Sign In — Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body>

<!-- ── TOP NAV ─────────────────────────────────────────────────────────────── -->
<nav class="top-nav">
    <a href="index.php" class="nav-logo">PASTIMES</a>
    <a href="login.php" class="nav-icon-btn" title="User Login">
        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    </a>
</nav>

<!-- ── MAIN CONTENT ──────────────────────────────────────────────────────────── -->
<main class="auth-page">

    <!-- Dark restricted-access hero -->
    <div class="auth-hero" style="background: #0F0A06;">
        <div style="position:absolute;inset:0;pointer-events:none;
                    background: repeating-linear-gradient(
                      45deg, rgba(191,139,48,0.03) 0, rgba(191,139,48,0.03) 1px,
                      transparent 0, transparent 50%);
                    background-size: 12px 12px;">
        </div>
        <div class="auth-hero-content">
            <div class="auth-logo" style="color:#fff;text-align:left;">PASTIMES</div>
            <div style="font-size:10px;font-weight:600;letter-spacing:0.2em;
                        text-transform:uppercase;color:var(--gold-light);margin-top:6px;">
                Admin · Restricted Access
            </div>
        </div>
    </div>

    <div class="auth-body">
    <div class="auth-card">

        <!-- Not authorised notice -->
        <?php if ($reason === 'not_authorised'): ?>
            <div class="alert alert-warning">You must sign in as an administrator to access that page.</div>
        <?php endif; ?>

        <!-- Error message -->
        <?php if ($errorMsg): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($errorMsg) ?>
            </div>
        <?php endif; ?>

        <!-- ═══════════════════════════════════════════════════════════════
             ADMIN LOGIN FORM
             Admins log in with username + password only (no email).
        ═══════════════════════════════════════════════════════════════ -->
        <form method="POST" action="adminLogin.php" novalidate>

            <!-- Username — sticky -->
            <div class="form-group">
                <label class="form-label" for="username">Admin Username *</label>
                <input
                    class="form-input <?= $errorMsg ? 'is-error' : '' ?>"
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Your admin username"
                    value="<?= $stickyUsername ?>"
                    required
                    autocomplete="username"
                />
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label" for="password">Password *</label>
                <div style="position:relative;">
                    <input
                        class="form-input <?= $errorMsg ? 'is-error' : '' ?>"
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Your password"
                        required
                        autocomplete="current-password"
                        style="padding-right:60px;"
                    />
                    <button type="button" class="pwd-toggle" data-target="password"
                        style="position:absolute;right:14px;top:50%;transform:translateY(-50%);
                               border:none;background:none;cursor:pointer;font-size:11px;
                               font-weight:600;color:var(--stone);letter-spacing:0.08em;">
                        Show
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full mt-8">
                Admin Sign In
            </button>

        </form>

        <div class="divider mt-24">Not an administrator?</div>
        <a href="login.php" class="btn btn-outline btn-full">Go to User Login</a>

    </div>
    </div><!-- end auth-body -->
</main>

<!-- ── FOOTER ─────────────────────────────────────────────────────────────── -->
<footer class="site-footer">
    &copy; <?= date('Y') ?> Pastimes — Pre-Loved Fashion
</footer>

<script src="js/main.js"></script>
</body>
</html>
