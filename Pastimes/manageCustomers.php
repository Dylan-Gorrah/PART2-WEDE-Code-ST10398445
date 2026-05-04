<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : manageCustomers.php
 * Description    : Admin CRUD page for Pastimes customers.
 *                  Handles: listing all customers, adding a new customer,
 *                  editing an existing customer, and deleting a customer.
 *                  All actions use OOP methods from AdminAuth class.
 */

session_start();
require_once 'DBConn.php';
require_once 'classes/AdminAuth.php';

$adminAuth = new AdminAuth($conn);
$adminAuth->requireLogin();

// ── Determine what mode we're in ─────────────────────────────────────────────
// ?action=add          → show the add form
// ?action=edit&id=X    → show the edit form for user X
// POST action=add      → process the add
// POST action=edit     → process the edit
// POST action=delete   → process delete (also handled in adminPanel.php)

$action  = $_GET['action']  ?? 'list';
$editID  = (int)($_GET['id'] ?? 0);

$msg     = '';
$msgType = '';
$editUser = null;

// ── Sticky form values ────────────────────────────────────────────────────────
$s = [
    'firstName' => '', 'lastName' => '', 'email' => '',
    'username'  => '', 'phone'    => '', 'status' => 'verified',
];

// ════════════════════════════════════════════════════════════════════════════
//  Handle POST submissions
// ════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['formAction'] ?? '';

    // Capture sticky values
    $s['firstName'] = htmlspecialchars(trim($_POST['firstName'] ?? ''));
    $s['lastName']  = htmlspecialchars(trim($_POST['lastName']  ?? ''));
    $s['email']     = htmlspecialchars(trim($_POST['email']     ?? ''));
    $s['username']  = htmlspecialchars(trim($_POST['username']  ?? ''));
    $s['phone']     = htmlspecialchars(trim($_POST['phone']     ?? ''));
    $s['status']    = $_POST['status'] ?? 'verified';

    if ($postAction === 'add') {
        $result  = $adminAuth->addCustomer($_POST);
        $msg     = $result['message'];
        $msgType = $result['success'] ? 'success' : 'error';
        if ($result['success']) {
            // Clear sticky on success, go back to list
            $action = 'list';
            $s = array_fill_keys(array_keys($s), '');
            $s['status'] = 'verified';
        } else {
            $action = 'add'; // Stay on form
        }

    } elseif ($postAction === 'edit') {
        $userID  = (int)($_POST['userID'] ?? 0);
        $result  = $adminAuth->updateCustomer($userID, $_POST);
        $msg     = $result['message'];
        $msgType = $result['success'] ? 'success' : 'error';
        if ($result['success']) {
            $action = 'list';
        } else {
            $action  = 'edit';
            $editID  = $userID;
        }

    } elseif ($postAction === 'delete') {
        $userID = (int)($_POST['userID'] ?? 0);
        if ($adminAuth->deleteCustomer($userID)) {
            $msg     = "Customer #$userID deleted successfully.";
            $msgType = 'success';
        } else {
            $msg     = "Could not delete customer #$userID.";
            $msgType = 'error';
        }
        $action = 'list';
    }
}

// ── If editing, pre-load the user's current data ─────────────────────────────
if ($action === 'edit' && $editID > 0 && empty($_POST)) {
    $editUser = $adminAuth->getUserById($editID);
    if ($editUser) {
        $s = [
            'firstName' => htmlspecialchars($editUser['firstName']),
            'lastName'  => htmlspecialchars($editUser['lastName']),
            'email'     => htmlspecialchars($editUser['email']),
            'username'  => htmlspecialchars($editUser['username']),
            'phone'     => htmlspecialchars($editUser['phone'] ?? ''),
            'status'    => $editUser['status'],
        ];
    } else {
        $msg     = "User not found.";
        $msgType = 'error';
        $action  = 'list';
    }
}

// ── Load users for list view ──────────────────────────────────────────────────
$allUsers = ($action === 'list') ? $adminAuth->getAllUsers() : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Manage Customers — Pastimes Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body style="margin:0;padding:0;">

<!-- ── ADMIN TOP BAR ─────────────────────────────────────────────────────── -->
<div class="admin-topbar">
    <span class="logo">PASTIMES</span>
    <span style="color:rgba(250,246,239,0.5);font-size:11px;letter-spacing:0.1em;text-transform:uppercase;">Customer Management</span>
    <span style="margin-left:auto;color:rgba(250,246,239,0.7);"><?= htmlspecialchars($_SESSION['adminName']) ?></span>
    <a href="logout.php" style="color:var(--gold-light);font-size:12px;font-weight:600;text-decoration:none;">Sign Out</a>
</div>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <a href="adminPanel.php" class="admin-nav-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="manageCustomers.php" class="admin-nav-link active">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            Customers
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
    </aside>

    <!-- MAIN CONTENT -->
    <main class="admin-content">

        <!-- Feedback message -->
        <?php if ($msg): ?>
            <div class="alert alert-<?= $msgType === 'success' ? 'success' : 'error' ?> auto-dismiss mb-24">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
        <!-- ══════════════════════════════════════════════════════════════════
             LIST VIEW — all customers
        ══════════════════════════════════════════════════════════════════ -->
        <div class="d-flex align-center justify-between mb-24">
            <div>
                <div class="admin-page-title">Customers</div>
                <div class="admin-page-sub">All registered users — edit, change status, or remove.</div>
            </div>
            <a href="manageCustomers.php?action=add" class="btn btn-primary">+ Add Customer</a>
        </div>

        <div class="card" style="padding:0;overflow:hidden;">
            <?php if (empty($allUsers)): ?>
                <div style="padding:32px;text-align:center;color:var(--stone);">
                    No customers found. <a href="manageCustomers.php?action=add">Add the first one.</a>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $user): ?>
                            <tr data-status="<?= htmlspecialchars($user['status']) ?>">
                                <td style="color:var(--stone);font-size:12px;">#<?= htmlspecialchars($user['userID']) ?></td>
                                <td><strong><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></strong></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['phone'] ?? '—') ?></td>
                                <td><span class="badge badge-<?= $user['status'] ?>"><?= $user['status'] ?></span></td>
                                <td style="font-size:12px;color:var(--stone);">
                                    <?= date('d M Y', strtotime($user['createdAt'])) ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-8">
                                        <a href="manageCustomers.php?action=edit&id=<?= $user['userID'] ?>"
                                           class="btn btn-outline btn-sm">Edit</a>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="formAction" value="delete"/>
                                            <input type="hidden" name="userID" value="<?= $user['userID'] ?>"/>
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                data-confirm="Delete <?= htmlspecialchars($user['firstName']) ?> permanently?">
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

        <?php elseif ($action === 'add'): ?>
        <!-- ══════════════════════════════════════════════════════════════════
             ADD CUSTOMER FORM
        ══════════════════════════════════════════════════════════════════ -->
        <div class="admin-page-title">Add New Customer</div>
        <div class="admin-page-sub">Fill in all required fields. The customer will be set to 'verified' by default.</div>

        <div class="card" style="max-width:600px;">
            <form method="POST" action="manageCustomers.php" novalidate>
                <input type="hidden" name="formAction" value="add"/>

                <div class="form-row mb-16">
                    <div class="form-group">
                        <label class="form-label">First Name *</label>
                        <input class="form-input" type="text" name="firstName" value="<?= $s['firstName'] ?>" required/>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name *</label>
                        <input class="form-input" type="text" name="lastName" value="<?= $s['lastName'] ?>" required/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input class="form-input" type="email" name="email" value="<?= $s['email'] ?>" required/>
                </div>

                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input class="form-input" type="text" name="username" value="<?= $s['username'] ?>" required/>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input class="form-input" type="tel" name="phone" value="<?= $s['phone'] ?>"/>
                </div>

                <div class="form-group">
                    <label class="form-label">Password * <span style="font-size:10px;font-weight:400;text-transform:none;">(min. 8 chars)</span></label>
                    <input class="form-input" type="password" name="password" placeholder="Temporary password" required minlength="8"/>
                </div>

                <div class="form-group">
                    <label class="form-label">Account Status</label>
                    <select class="form-select" name="status">
                        <option value="verified"  <?= $s['status']==='verified'  ? 'selected' : '' ?>>Verified</option>
                        <option value="pending"   <?= $s['status']==='pending'   ? 'selected' : '' ?>>Pending</option>
                        <option value="rejected"  <?= $s['status']==='rejected'  ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>

                <div class="d-flex gap-8 mt-24">
                    <button type="submit" class="btn btn-primary">Add Customer</button>
                    <a href="manageCustomers.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>

        <?php elseif ($action === 'edit'): ?>
        <!-- ══════════════════════════════════════════════════════════════════
             EDIT CUSTOMER FORM
        ══════════════════════════════════════════════════════════════════ -->
        <div class="admin-page-title">Edit Customer</div>
        <div class="admin-page-sub">Modify the details for this user. Password is not changed here.</div>

        <div class="card" style="max-width:600px;">
            <form method="POST" action="manageCustomers.php" novalidate>
                <input type="hidden" name="formAction" value="edit"/>
                <input type="hidden" name="userID" value="<?= $editID ?>"/>

                <div class="form-row mb-16">
                    <div class="form-group">
                        <label class="form-label">First Name *</label>
                        <input class="form-input" type="text" name="firstName" value="<?= $s['firstName'] ?>" required/>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name *</label>
                        <input class="form-input" type="text" name="lastName" value="<?= $s['lastName'] ?>" required/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input class="form-input" type="email" name="email" value="<?= $s['email'] ?>" required/>
                </div>

                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input class="form-input" type="text" name="username" value="<?= $s['username'] ?>" required/>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input class="form-input" type="tel" name="phone" value="<?= $s['phone'] ?>"/>
                </div>

                <!-- Status — this is where admin can flip pending → verified -->
                <div class="form-group">
                    <label class="form-label">Account Status</label>
                    <select class="form-select" name="status" style="height:48px;">
                        <option value="pending"  <?= $s['status']==='pending'  ? 'selected' : '' ?>>Pending</option>
                        <option value="verified" <?= $s['status']==='verified' ? 'selected' : '' ?>>Verified</option>
                        <option value="rejected" <?= $s['status']==='rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                    <small style="color:var(--stone);font-size:11px;margin-top:4px;display:block;">
                        Change to "Verified" to allow this user to log in.
                    </small>
                </div>

                <div class="d-flex gap-8 mt-24">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="manageCustomers.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>

        <?php endif; ?>

    </main>
</div>

<script src="js/main.js"></script>
</body>
</html>
