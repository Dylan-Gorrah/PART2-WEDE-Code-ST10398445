<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : login.php
 * Description    : User login page for Pastimes.
 *                  Accepts username + email + password.
 *                  Password compared to stored hash using password_verify().
 *                  Sticky form on failure — fields repopulate (except password).
 *                  On success: displays user data in associative table and
 *                  a "User [Name] is logged in" string as required by the brief.
 *
 * References:
 *   password_verify() — https://www.php.net/manual/en/function.password-verify.php
 *   session_start()   — https://www.php.net/manual/en/function.session-start.php
 */

session_start();

// If already logged in, skip the login page entirely
if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
    header("Location: dashboard.php");
    exit();
}

// Include DB connection and OOP auth class
require_once 'DBConn.php';
require_once 'classes/UserAuth.php';

// Instantiate the UserAuth class (Object-Oriented PHP)
$auth = new UserAuth($conn);

// ── Sticky form variables — repopulate on login failure ──────────────────────
$stickyUsername = '';
$stickyEmail    = '';

// ── Response variables ───────────────────────────────────────────────────────
$errorMsg   = '';
$successMsg = '';
$loggedInUser = null;   // Will hold the full user row on successful login

// ── Handle reason parameter (e.g. session expired redirect) ─────────────────
$reason = $_GET['reason'] ?? '';

// ════════════════════════════════════════════════════════════════════════════
//  Handle POST — when the form is submitted
// ════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Capture for sticky form (never sticky the password — security)
    $stickyUsername = htmlspecialchars(trim($_POST['username'] ?? ''));
    $stickyEmail    = htmlspecialchars(trim($_POST['email']    ?? ''));
    $rawPassword    = $_POST['password'] ?? '';

    // Call login() on the OOP UserAuth class
    $result = $auth->login($stickyUsername, $stickyEmail, $rawPassword);

    if ($result['success']) {
        $loggedInUser = $result['user'];
        $successMsg   = $result['message'];
        // Session is already set inside UserAuth::login()
        // We stay on this page briefly to show user data, then redirect
        // The brief asks to "display the user's data" and show the login string
    } else {
        $errorMsg = $result['message'];
        // Sticky: username and email stay in the form — user doesn't retype
        // Password field intentionally cleared for security
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Sign In — Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body>

<!-- ── TOP NAV ─────────────────────────────────────────────────────────────── -->
<nav class="top-nav">
    <a href="index.php" class="nav-logo">PASTIMES</a>
    <a href="adminLogin.php" class="nav-icon-btn" title="Admin Panel">
        <!-- Shield icon -->
        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </a>
</nav>

<!-- ── MAIN CONTENT ──────────────────────────────────────────────────────────── -->
<main class="auth-page">

    <?php if ($loggedInUser): ?>
    <!-- ═══════════════════════════════════════════════════════════════════════
         SUCCESS STATE — show user data and the required "User X is logged in"
         string. The brief requires displaying data using an associative approach
         (column names as labels in a table).
    ════════════════════════════════════════════════════════════════════════ -->
    <div style="width:100%;max-width:640px;">

        <!-- Required string: "User [Name] is logged in" -->
        <div class="welcome-banner mb-24" style="border-radius:12px;">
            <div class="welcome-name">
                User <?= htmlspecialchars($loggedInUser['firstName'] . ' ' . $loggedInUser['lastName']) ?> is logged in
            </div>
            <div class="welcome-sub">Welcome back to Pastimes. Your account is verified and active.</div>
        </div>

        <!-- User data displayed using associative column names (as per brief) -->
        <div class="card">
            <div class="card-title">Your Account Details</div>
            <div class="card-subtitle">Retrieved from the ClothingStore database · tblUser</div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Column</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Displaying each column name (associative key) and its value -->
                    <tr>
                        <td><strong>userID</strong></td>
                        <td><?= htmlspecialchars($loggedInUser['userID']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>firstName</strong></td>
                        <td><?= htmlspecialchars($loggedInUser['firstName']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>lastName</strong></td>
                        <td><?= htmlspecialchars($loggedInUser['lastName']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>email</strong></td>
                        <td><?= htmlspecialchars($loggedInUser['email']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>username</strong></td>
                        <td><?= htmlspecialchars($loggedInUser['username']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>phone</strong></td>
                        <td><?= htmlspecialchars($loggedInUser['phone'] ?? 'Not provided') ?></td>
                    </tr>
                    <tr>
                        <td><strong>status</strong></td>
                        <td><span class="badge badge-verified"><?= htmlspecialchars($loggedInUser['status']) ?></span></td>
                    </tr>
                    <tr>
                        <td><strong>deliveryAddress</strong></td>
                        <td>
                            <?php if (!empty($loggedInUser['deliveryAddress'])): ?>
                                <?= htmlspecialchars($loggedInUser['deliveryAddress']) ?>
                            <?php else: ?>
                                <a href="delivery.php">+ Add delivery address</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>createdAt</strong></td>
                        <td><?= htmlspecialchars($loggedInUser['createdAt']) ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="d-flex gap-8 mt-24">
                <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                <a href="delivery.php" class="btn btn-outline">Edit Delivery Address</a>
                <a href="logout.php" class="btn btn-outline" style="margin-left:auto;">Sign Out</a>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- ═══════════════════════════════════════════════════════════════════════
         DEFAULT STATE — the login form with auth-hero + auth-body
    ════════════════════════════════════════════════════════════════════════ -->

    <!-- Hero image header -->
    <div class="auth-hero">
        <div class="auth-hero-overlay"></div>
        <div class="auth-hero-content">
            <div class="auth-logo" style="color:#fff;text-align:left;">PASTIMES</div>
            <div class="auth-tagline" style="color:rgba(250,246,239,0.7);text-align:left;margin-bottom:0;">Pre-Loved Fashion · South Africa</div>
        </div>
    </div>

    <div class="auth-body">
    <div class="auth-card">

        <!-- Tab switcher -->
        <div class="tab-switcher">
            <button class="tab-btn active">Sign In</button>
            <button class="tab-btn" onclick="window.location='register.php'">Create Account</button>
        </div>

        <!-- Session expired notice -->
        <?php if ($reason === 'session_expired'): ?>
            <div class="alert alert-warning">Your session expired. Please sign in again.</div>
        <?php endif; ?>

        <!-- Error message (sticky form) -->
        <?php if ($errorMsg): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($errorMsg) ?>
            </div>
        <?php endif; ?>

        <!-- ═══════════════════════════════════════════════════════════════
             LOGIN FORM
             Brief: Accept username and email address.
             Password compared to hash in tblUser.
             Sticky form on failure (username + email repopulate).
        ═══════════════════════════════════════════════════════════════ -->
        <form method="POST" action="login.php" novalidate>

            <!-- Username — sticky: repopulates after failed login -->
            <div class="form-group">
                <label class="form-label" for="username">Username *</label>
                <input
                    class="form-input <?= $errorMsg ? 'is-error' : '' ?>"
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Your username"
                    value="<?= $stickyUsername ?>"
                    required
                    autocomplete="username"
                />
            </div>

            <!-- Email — sticky: repopulates after failed login -->
            <div class="form-group">
                <label class="form-label" for="email">Email Address *</label>
                <input
                    class="form-input <?= $errorMsg ? 'is-error' : '' ?>"
                    type="email"
                    id="email"
                    name="email"
                    placeholder="you@example.com"
                    value="<?= $stickyEmail ?>"
                    required
                    autocomplete="email"
                />
            </div>

            <!-- Password — never sticky (security) -->
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
                Sign In
            </button>

        </form>

        <div class="divider mt-24">Don't have an account?</div>
        <a href="register.php" class="btn btn-outline btn-full">Create Account</a>

        <!-- Admin link -->
        <p class="text-center mt-16" style="font-size:12px;color:var(--stone);">
            Administrator? <a href="adminLogin.php">Admin Login →</a>
        </p>

    </div>
    </div><!-- end auth-body -->
    <?php endif; ?>

</main>

<!-- ── FOOTER ─────────────────────────────────────────────────────────────── -->
<footer class="site-footer">
    &copy; <?= date('Y') ?> Pastimes — Pre-Loved Fashion
</footer>

<script src="js/main.js"></script>
</body>
</html>
