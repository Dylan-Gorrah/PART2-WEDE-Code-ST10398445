<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : delivery.php
 * Description    : Allows a logged-in user to add or update their delivery address.
 *                  Supports residential or work address type.
 *                  Saves to the deliveryAddress column in tblUser.
 */

session_start();
require_once 'DBConn.php';
require_once 'classes/UserAuth.php';

// OOP auth — redirect to login if not logged in
$auth = new UserAuth($conn);
$auth->requireLogin();

// Fetch current user data
$user = $auth->getUserById($_SESSION['userID']);

// If DB was reset, old userIDs no longer exist
if (!$user) {
    session_destroy();
    header("Location: login.php?reason=session_expired");
    exit();
}

// ── Response variables ────────────────────────────────────────────────────────
$successMsg = '';
$errorMsg   = '';

// ── Sticky form values — pre-fill from existing data or POST ─────────────────
// Parse existing address if it exists
$existingAddress = $user['deliveryAddress'] ?? '';

// Try to extract address type from stored address (we prefix it: "RESIDENTIAL: ...")
$stickyType    = 'residential';
$stickyStreet  = '';
$stickySuburb  = '';
$stickyCity    = '';
$stickyProvince= '';
$stickyCode    = '';
$stickyNotes   = '';

// If an address is already saved, parse the prefixed format back into fields
if (!empty($existingAddress)) {
    $lines = explode("\n", $existingAddress);
    foreach ($lines as $line) {
        if (str_starts_with($line, 'TYPE:'))     $stickyType     = trim(str_replace('TYPE:', '', $line));
        if (str_starts_with($line, 'STREET:'))   $stickyStreet   = trim(str_replace('STREET:', '', $line));
        if (str_starts_with($line, 'SUBURB:'))   $stickySuburb   = trim(str_replace('SUBURB:', '', $line));
        if (str_starts_with($line, 'CITY:'))     $stickyCity     = trim(str_replace('CITY:', '', $line));
        if (str_starts_with($line, 'PROVINCE:')) $stickyProvince = trim(str_replace('PROVINCE:', '', $line));
        if (str_starts_with($line, 'CODE:'))     $stickyCode     = trim(str_replace('CODE:', '', $line));
        if (str_starts_with($line, 'NOTES:'))    $stickyNotes    = trim(str_replace('NOTES:', '', $line));
    }
}

// ════════════════════════════════════════════════════════════════════════════
//  Handle POST — save the delivery address
// ════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture and sanitise input
    $stickyType     = htmlspecialchars(trim($_POST['addressType'] ?? 'residential'));
    $stickyStreet   = htmlspecialchars(trim($_POST['street']     ?? ''));
    $stickySuburb   = htmlspecialchars(trim($_POST['suburb']     ?? ''));
    $stickyCity     = htmlspecialchars(trim($_POST['city']       ?? ''));
    $stickyProvince = htmlspecialchars(trim($_POST['province']   ?? ''));
    $stickyCode     = htmlspecialchars(trim($_POST['postalCode'] ?? ''));
    $stickyNotes    = htmlspecialchars(trim($_POST['notes']      ?? ''));

    // Validate required fields
    $errors = [];
    if (empty($stickyStreet))   $errors[] = "Street address is required.";
    if (empty($stickySuburb))   $errors[] = "Suburb is required.";
    if (empty($stickyCity))     $errors[] = "City is required.";
    if (empty($stickyProvince)) $errors[] = "Province is required.";
    if (empty($stickyCode))     $errors[] = "Postal code is required.";

    if (!empty($errors)) {
        $errorMsg = implode(' ', $errors);
    } else {
        // Build a structured string to store in the deliveryAddress column
        $addressString  = "TYPE: $stickyType\n";
        $addressString .= "STREET: $stickyStreet\n";
        $addressString .= "SUBURB: $stickySuburb\n";
        $addressString .= "CITY: $stickyCity\n";
        $addressString .= "PROVINCE: $stickyProvince\n";
        $addressString .= "CODE: $stickyCode";
        if (!empty($stickyNotes)) {
            $addressString .= "\nNOTES: $stickyNotes";
        }

        // Save to the database using a prepared statement
        $userID = $_SESSION['userID'];
        $stmt   = $conn->prepare("UPDATE tblUser SET deliveryAddress = ? WHERE userID = ?");
        $stmt->bind_param("si", $addressString, $userID);

        if ($stmt->execute()) {
            $successMsg = "Delivery address saved successfully!";
            // Refresh the user object so the page shows the new address
            $user = $auth->getUserById($userID);
        } else {
            $errorMsg = "Could not save address. Please try again. (" . $conn->error . ")";
        }
        $stmt->close();
    }
}

// South African provinces for the dropdown
$provinces = [
    'Gauteng', 'Western Cape', 'Eastern Cape', 'KwaZulu-Natal',
    'Limpopo', 'Mpumalanga', 'North West', 'Northern Cape', 'Free State'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Delivery Address — Pastimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body>

<!-- ── TOP NAV ─────────────────────────────────────────────────────────────── -->
<nav class="top-nav">
    <a href="dashboard.php" class="nav-icon-btn" title="Back to Dashboard">
        <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    </a>
    <a href="index.php" class="nav-logo">PASTIMES</a>
    <div class="nav-user-pill">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <?= htmlspecialchars($_SESSION['name']) ?>
    </div>
</nav>

<!-- ── MAIN CONTENT ──────────────────────────────────────────────────────────── -->
<div class="page-wrap">
<div class="container-sm" style="padding-top:40px;padding-bottom:60px;">

    <!-- Page heading -->
    <h1 style="font-family:var(--serif);font-size:2rem;font-weight:400;margin-bottom:4px;">
        Delivery Address
    </h1>
    <p style="font-size:13px;color:var(--stone);margin-bottom:28px;">
        Your parcel will be sent to this address. Select residential or work.
    </p>

    <!-- ── SUCCESS / ERROR MESSAGES ─────────────────────────────────────── -->
    <?php if ($successMsg): ?>
        <div class="alert alert-success auto-dismiss"><?= htmlspecialchars($successMsg) ?></div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════════════
         DELIVERY FORM
    ════════════════════════════════════════════════════════════════════ -->
    <div class="card">
        <form method="POST" action="delivery.php" novalidate>

            <!-- Address type selector -->
            <div class="form-group">
                <label class="form-label">Address Type *</label>
                <div style="display:flex;gap:10px;">
                    <!-- Residential toggle button -->
                    <label style="flex:1;cursor:pointer;">
                        <input type="radio" name="addressType" value="residential"
                               <?= ($stickyType === 'residential') ? 'checked' : '' ?>
                               style="display:none;" class="addr-radio"/>
                        <div class="addr-type-btn" data-value="residential"
                             style="border:1.5px solid var(--<?= $stickyType==='residential' ? 'gold' : 'stone-pale' ?>);
                                    border-radius:var(--radius);padding:14px 16px;text-align:center;
                                    background:var(--<?= $stickyType==='residential' ? 'gold-pale' : 'ivory' ?>);
                                    transition:all 0.2s;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;margin:0 auto 6px;"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            <div style="font-size:11px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;">Residential</div>
                        </div>
                    </label>
                    <!-- Work toggle button -->
                    <label style="flex:1;cursor:pointer;">
                        <input type="radio" name="addressType" value="work"
                               <?= ($stickyType === 'work') ? 'checked' : '' ?>
                               style="display:none;" class="addr-radio"/>
                        <div class="addr-type-btn" data-value="work"
                             style="border:1.5px solid var(--<?= $stickyType==='work' ? 'gold' : 'stone-pale' ?>);
                                    border-radius:var(--radius);padding:14px 16px;text-align:center;
                                    background:var(--<?= $stickyType==='work' ? 'gold-pale' : 'ivory' ?>);
                                    transition:all 0.2s;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;margin:0 auto 6px;"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                            <div style="font-size:11px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;">Work</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Street address -->
            <div class="form-group">
                <label class="form-label" for="street">Street Address *</label>
                <input class="form-input" type="text" id="street" name="street"
                       placeholder="12 Sandton Drive"
                       value="<?= htmlspecialchars($stickyStreet) ?>" required/>
            </div>

            <!-- Suburb + City row -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="suburb">Suburb *</label>
                    <input class="form-input" type="text" id="suburb" name="suburb"
                           placeholder="Sandton"
                           value="<?= htmlspecialchars($stickySuburb) ?>" required/>
                </div>
                <div class="form-group">
                    <label class="form-label" for="city">City *</label>
                    <input class="form-input" type="text" id="city" name="city"
                           placeholder="Johannesburg"
                           value="<?= htmlspecialchars($stickyCity) ?>" required/>
                </div>
            </div>

            <!-- Province + Postal Code row -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="province">Province *</label>
                    <select class="form-select" id="province" name="province" required style="height:48px;">
                        <option value="">Select province...</option>
                        <?php foreach ($provinces as $prov): ?>
                            <option value="<?= $prov ?>" <?= ($stickyProvince === $prov) ? 'selected' : '' ?>>
                                <?= $prov ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="postalCode">Postal Code *</label>
                    <input class="form-input" type="text" id="postalCode" name="postalCode"
                           placeholder="2196"
                           value="<?= htmlspecialchars($stickyCode) ?>"
                           required maxlength="10"/>
                </div>
            </div>

            <!-- Special instructions (optional) -->
            <div class="form-group">
                <label class="form-label" for="notes">
                    Delivery Notes
                    <span style="font-size:10px;font-weight:400;text-transform:none;">(optional)</span>
                </label>
                <textarea class="form-textarea" id="notes" name="notes"
                          placeholder="e.g. Leave at security gate, ring bell 3 times..."
                          rows="3"><?= htmlspecialchars($stickyNotes) ?></textarea>
            </div>

            <div class="d-flex gap-8 mt-8">
                <button type="submit" class="btn btn-primary">Save Address</button>
                <a href="dashboard.php" class="btn btn-outline">Cancel</a>
            </div>

        </form>
    </div>

    <!-- Currently saved address preview -->
    <?php if (!empty($user['deliveryAddress'])):
        $lines = explode("\n", $user['deliveryAddress']);
        $fields = [];
        foreach ($lines as $line) {
            [$key, $val] = array_pad(explode(':', $line, 2), 2, '');
            $fields[trim($key)] = trim($val);
        }
    ?>
    <div class="card mt-24" style="border-left: 3px solid var(--gold);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <div style="font-size:10px;font-weight:700;letter-spacing:0.14em;
                            text-transform:uppercase;color:var(--stone);margin-bottom:8px;">
                    Currently Saved Address
                </div>
                <div style="font-size:14px;color:var(--espresso);line-height:1.9;">
                    <?= htmlspecialchars($fields['STREET'] ?? '') ?><br>
                    <?= htmlspecialchars(($fields['SUBURB'] ?? '') . ', ' . ($fields['CITY'] ?? '')) ?><br>
                    <?= htmlspecialchars(($fields['PROVINCE'] ?? '') . ' · ' . ($fields['CODE'] ?? '')) ?>
                </div>
            </div>
            <span class="badge" style="background:var(--gold-pale);color:var(--espresso-light);flex-shrink:0;">
                <?= htmlspecialchars($fields['TYPE'] ?? 'Residential') ?>
            </span>
        </div>
    </div>
    <?php endif; ?>

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
<script>
// Toggle address type button styles when radio changes
document.querySelectorAll('.addr-radio').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.addr-type-btn').forEach(function(btn) {
            btn.style.borderColor  = 'var(--stone-pale)';
            btn.style.background   = 'var(--ivory)';
        });
        var activeBtn = document.querySelector('.addr-type-btn[data-value="' + radio.value + '"]');
        if (activeBtn) {
            activeBtn.style.borderColor = 'var(--gold)';
            activeBtn.style.background  = 'var(--gold-pale)';
        }
    });
});
</script>
</body>
</html>
