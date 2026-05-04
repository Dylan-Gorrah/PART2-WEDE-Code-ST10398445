<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : register.php
 * Description    : New user registration page for Pastimes.
 *                  Validates all fields server-side, hashes password with
 *                  password_hash(), saves user with status='pending'.
 *                  Uses sticky form to repopulate fields on validation failure.
 *
 * References:
 *   password_hash() — https://www.php.net/manual/en/function.password-hash.php
 *   MySQLi prepared statements — https://www.php.net/manual/en/mysqli.prepare.php
 *   filter_var FILTER_VALIDATE_EMAIL — https://www.php.net/manual/en/filter.constants.php
 */

session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
    header("Location: dashboard.php");
    exit();
}

// Include the DB connection and the OOP auth class
require_once 'DBConn.php';
require_once 'classes/UserAuth.php';

// Create an instance of our UserAuth class (Object-Oriented PHP)
$auth = new UserAuth($conn);

// ── Variables for sticky form (repopulate on error) ──────────────────────────
$stickyFirstName = '';
$stickyLastName  = '';
$stickyEmail     = '';
$stickyUsername  = '';
$stickyPhone     = '';

// ── Response message variables ───────────────────────────────────────────────
$errorMsg   = '';
$successMsg = '';

// ════════════════════════════════════════════════════════════════════════════
//  Handle the POST request when the form is submitted
// ════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Capture submitted values for sticky form behaviour
    $stickyFirstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
    $stickyLastName  = htmlspecialchars(trim($_POST['lastName']  ?? ''));
    $stickyEmail     = htmlspecialchars(trim($_POST['email']     ?? ''));
    $stickyUsername  = htmlspecialchars(trim($_POST['username']  ?? ''));
    $stickyPhone     = htmlspecialchars(trim($_POST['phone']     ?? ''));

    // Call the register() method on our UserAuth object (OOP in action)
    $result = $auth->register($_POST);

    if ($result['success']) {
        $successMsg      = $result['message'];
        // Clear sticky values on success — no need to repopulate
        $stickyFirstName = $stickyLastName = $stickyEmail = $stickyUsername = $stickyPhone = '';
    } else {
        $errorMsg = $result['message'];
        // Sticky values remain so user doesn't retype everything
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Create Account — Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body>

<!-- ── TOP NAV ─────────────────────────────────────────────────────────────── -->
<nav class="top-nav">
    <a href="index.php" class="nav-logo">PASTIMES</a>
    <a href="login.php" class="nav-icon-btn" title="Sign In">
        <!-- Person icon -->
        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    </a>
</nav>

<!-- ── MAIN CONTENT ──────────────────────────────────────────────────────────── -->
<main class="auth-page">

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
            <button class="tab-btn" data-tab="login" onclick="window.location='login.php'">Sign In</button>
            <button class="tab-btn active" data-tab="register">Create Account</button>
        </div>

        <!-- ── SUCCESS MESSAGE ──────────────────────────────────────────────── -->
        <?php if ($successMsg): ?>
            <div class="alert alert-success">
                <?= $successMsg ?>
            </div>
            <p class="text-center mt-16" style="font-size:14px;">
                <a href="login.php">← Return to Sign In</a>
            </p>
        <?php else: ?>

        <!-- ── ERROR MESSAGE ────────────────────────────────────────────────── -->
        <?php if ($errorMsg): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($errorMsg) ?>
            </div>
        <?php endif; ?>

        <!-- ═══════════════════════════════════════════════════════════════════
             REGISTRATION FORM
             HTML5 required attributes provide first layer of validation.
             PHP (UserAuth::register) provides the server-side second layer.
        ════════════════════════════════════════════════════════════════════ -->
        <form method="POST" action="register.php" novalidate>

            <!-- Name row -->
            <div class="form-row mb-16">
                <div class="form-group">
                    <label class="form-label" for="firstName">First Name *</label>
                    <input
                        class="form-input"
                        type="text"
                        id="firstName"
                        name="firstName"
                        placeholder="Thabo"
                        value="<?= $stickyFirstName ?>"
                        required
                        maxlength="100"
                    />
                </div>
                <div class="form-group">
                    <label class="form-label" for="lastName">Last Name *</label>
                    <input
                        class="form-input"
                        type="text"
                        id="lastName"
                        name="lastName"
                        placeholder="Mokoena"
                        value="<?= $stickyLastName ?>"
                        required
                        maxlength="100"
                    />
                </div>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label class="form-label" for="email">Email Address *</label>
                <input
                    class="form-input"
                    type="email"
                    id="email"
                    name="email"
                    placeholder="you@example.com"
                    value="<?= $stickyEmail ?>"
                    required
                    maxlength="255"
                />
            </div>

            <!-- Username -->
            <div class="form-group">
                <label class="form-label" for="username">Username *</label>
                <input
                    class="form-input"
                    type="text"
                    id="username"
                    name="username"
                    placeholder="thabom"
                    value="<?= $stickyUsername ?>"
                    required
                    maxlength="100"
                />
            </div>

            <!-- Phone -->
            <div class="form-group">
                <label class="form-label" for="phone">Phone Number *</label>
                <input
                    class="form-input"
                    type="tel"
                    id="phone"
                    name="phone"
                    placeholder="+27 82 000 0000"
                    value="<?= $stickyPhone ?>"
                    required
                    maxlength="20"
                />
            </div>

            <!-- Role selector pills -->
            <div class="form-group">
                <label class="form-label">I want to *</label>
                <div style="display:flex; gap:8px;">
                    <button type="button" class="pill active" data-role="buy"
                            onclick="setRole(this,'buy')">Buy</button>
                    <button type="button" class="pill" data-role="sell"
                            onclick="setRole(this,'sell')">Sell</button>
                    <button type="button" class="pill" data-role="both"
                            onclick="setRole(this,'both')">Both</button>
                </div>
                <input type="hidden" name="userRole" id="userRole" value="buy"/>
            </div>

            <!-- Password — brief requires minimum 8 characters -->
            <div class="form-group">
                <label class="form-label" for="password">
                    Password * <span style="font-size:10px;text-transform:none;font-weight:400;color:var(--stone);">(min. 8 characters)</span>
                </label>
                <div style="position:relative;">
                    <input
                        class="form-input"
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create a secure password"
                        required
                        minlength="8"
                        style="padding-right:60px;"
                    />
                    <button type="button" class="pwd-toggle" data-target="password"
                        style="position:absolute;right:14px;top:50%;transform:translateY(-50%);
                               border:none;background:none;cursor:pointer;font-size:11px;
                               font-weight:600;color:var(--stone);letter-spacing:0.08em;">
                        Show
                    </button>
                </div>
                <!-- Password strength bar -->
                <div style="margin-top:6px;height:3px;background:var(--stone-pale);border-radius:2px;overflow:hidden;">
                    <div id="pwd-strength-bar" style="height:100%;width:0;transition:width 0.3s,background-color 0.3s;border-radius:2px;"></div>
                </div>
                <span id="pwd-strength-text" style="font-size:10px;font-weight:600;letter-spacing:0.08em;"></span>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label class="form-label" for="confirmPassword">Confirm Password *</label>
                <div style="position:relative;">
                    <input
                        class="form-input"
                        type="password"
                        id="confirmPassword"
                        name="confirmPassword"
                        placeholder="Repeat your password"
                        required
                        minlength="8"
                        style="padding-right:60px;"
                    />
                    <button type="button" class="pwd-toggle" data-target="confirmPassword"
                        style="position:absolute;right:14px;top:50%;transform:translateY(-50%);
                               border:none;background:none;cursor:pointer;font-size:11px;
                               font-weight:600;color:var(--stone);letter-spacing:0.08em;">
                        Show
                    </button>
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn btn-primary btn-full mt-8">
                Create Account
            </button>

        </form>

        <div class="divider mt-24">Already have an account?</div>
        <a href="login.php" class="btn btn-outline btn-full">Sign In Instead</a>

        <?php endif; ?>
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
