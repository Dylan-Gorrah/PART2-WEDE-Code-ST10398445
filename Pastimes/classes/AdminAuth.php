<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : classes/AdminAuth.php
 * Description    : Object-Oriented class that handles all administrator
 *                  authentication, user verification/rejection, and
 *                  customer CRUD operations.
 */

class AdminAuth {
    // ── Property: the MySQLi connection ──────────────────────────────────────
    private $conn;

    // ── Constructor ──────────────────────────────────────────────────────────
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  ADMIN LOGIN — check credentials against tblAdmin
    //  Returns: array ['success' => bool, 'message' => string, 'admin' => array|null]
    // ════════════════════════════════════════════════════════════════════════
    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => "Username and password are required.", 'admin' => null];
        }

        // Look up admin by username only (no email needed for admin login)
        $stmt = $this->conn->prepare(
            "SELECT adminID, firstName, lastName, email, username, password
             FROM tblAdmin WHERE username = ?"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => "No admin account found with that username.", 'admin' => null];
        }

        $admin = $result->fetch_assoc();
        $stmt->close();

        // Compare password against the stored bcrypt hash
        if (!password_verify($password, $admin['password'])) {
            return ['success' => false, 'message' => "Incorrect password.", 'admin' => null];
        }

        // Set admin session
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['adminLoggedIn'] = true;
        $_SESSION['adminID']       = $admin['adminID'];
        $_SESSION['adminName']     = $admin['firstName'] . ' ' . $admin['lastName'];
        $_SESSION['adminUsername'] = $admin['username'];

        return ['success' => true, 'message' => "Welcome, " . $admin['firstName'] . "!", 'admin' => $admin];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  IS ADMIN LOGGED IN
    // ════════════════════════════════════════════════════════════════════════
    public function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['adminLoggedIn']) && $_SESSION['adminLoggedIn'] === true;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  REQUIRE ADMIN LOGIN — redirect to adminLogin.php if not logged in
    // ════════════════════════════════════════════════════════════════════════
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header("Location: adminLogin.php?reason=not_authorised");
            exit();
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    //  GET ALL USERS — returns all rows from tblUser (for admin management)
    // ════════════════════════════════════════════════════════════════════════
    public function getAllUsers($filterStatus = null) {
        if ($filterStatus) {
            $stmt = $this->conn->prepare(
                "SELECT userID, firstName, lastName, email, username, phone, status, createdAt
                 FROM tblUser WHERE status = ? ORDER BY createdAt DESC"
            );
            $stmt->bind_param("s", $filterStatus);
        } else {
            $stmt = $this->conn->prepare(
                "SELECT userID, firstName, lastName, email, username, phone, status, createdAt
                 FROM tblUser ORDER BY createdAt DESC"
            );
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $users  = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
        return $users;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  GET USER BY ID — single user row
    // ════════════════════════════════════════════════════════════════════════
    public function getUserById($userID) {
        $stmt = $this->conn->prepare(
            "SELECT userID, firstName, lastName, email, username, phone, status, deliveryAddress, createdAt
             FROM tblUser WHERE userID = ?"
        );
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  UPDATE USER STATUS — 'verified' or 'rejected'
    //  This is the core admin action: approving/rejecting new registrations
    // ════════════════════════════════════════════════════════════════════════
    public function updateUserStatus($userID, $status) {
        $allowed = ['pending', 'verified', 'rejected'];
        if (!in_array($status, $allowed)) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE tblUser SET status = ? WHERE userID = ?");
        $stmt->bind_param("si", $status, $userID);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  ADD CUSTOMER — admin adds a user manually
    // ════════════════════════════════════════════════════════════════════════
    public function addCustomer($data) {
        $firstName = $this->sanitise($data['firstName'] ?? '');
        $lastName  = $this->sanitise($data['lastName']  ?? '');
        $email     = $this->sanitise($data['email']     ?? '');
        $username  = $this->sanitise($data['username']  ?? '');
        $phone     = $this->sanitise($data['phone']     ?? '');
        $password  = $data['password'] ?? '';
        $status    = in_array($data['status'] ?? '', ['pending','verified','rejected'])
                     ? $data['status'] : 'verified'; // Admin-added users default verified

        if (empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($password)) {
            return ['success' => false, 'message' => "All required fields must be filled in."];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => "Password must be at least 8 characters."];
        }

        // Check for duplicates
        $stmt = $this->conn->prepare("SELECT userID FROM tblUser WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => "Email or username already exists."];
        }
        $stmt->close();

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare(
            "INSERT INTO tblUser (firstName, lastName, email, username, password, phone, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssssss", $firstName, $lastName, $email, $username, $hashed, $phone, $status);

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => "Customer $firstName $lastName added successfully."];
        }

        $stmt->close();
        return ['success' => false, 'message' => "Failed to add customer: " . $this->conn->error];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  UPDATE CUSTOMER — admin edits a user's details
    // ════════════════════════════════════════════════════════════════════════
    public function updateCustomer($userID, $data) {
        $firstName = $this->sanitise($data['firstName'] ?? '');
        $lastName  = $this->sanitise($data['lastName']  ?? '');
        $email     = $this->sanitise($data['email']     ?? '');
        $username  = $this->sanitise($data['username']  ?? '');
        $phone     = $this->sanitise($data['phone']     ?? '');
        $status    = in_array($data['status'] ?? '', ['pending','verified','rejected'])
                     ? $data['status'] : 'pending';

        if (empty($firstName) || empty($lastName) || empty($email) || empty($username)) {
            return ['success' => false, 'message' => "Required fields cannot be empty."];
        }

        $stmt = $this->conn->prepare(
            "UPDATE tblUser
             SET firstName = ?, lastName = ?, email = ?, username = ?, phone = ?, status = ?
             WHERE userID = ?"
        );
        $stmt->bind_param("ssssssi", $firstName, $lastName, $email, $username, $phone, $status, $userID);

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => "Customer updated successfully."];
        }

        $stmt->close();
        return ['success' => false, 'message' => "Update failed: " . $this->conn->error];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  DELETE CUSTOMER — admin removes a user
    // ════════════════════════════════════════════════════════════════════════
    public function deleteCustomer($userID) {
        $stmt = $this->conn->prepare("DELETE FROM tblUser WHERE userID = ?");
        $stmt->bind_param("i", $userID);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  GET STATS — summary numbers for the admin dashboard
    // ════════════════════════════════════════════════════════════════════════
    public function getStats() {
        $stats = [];

        $tables = [
            'totalUsers'    => "SELECT COUNT(*) AS n FROM tblUser",
            'pendingUsers'  => "SELECT COUNT(*) AS n FROM tblUser WHERE status = 'pending'",
            'verifiedUsers' => "SELECT COUNT(*) AS n FROM tblUser WHERE status = 'verified'",
            'totalClothes'  => "SELECT COUNT(*) AS n FROM tblClothes",
            'availableItems'=> "SELECT COUNT(*) AS n FROM tblClothes WHERE status = 'available'",
            'totalOrders'   => "SELECT COUNT(*) AS n FROM tblAorder",
        ];

        foreach ($tables as $key => $sql) {
            $result = $this->conn->query($sql);
            if ($result) {
                $row = $result->fetch_assoc();
                $stats[$key] = $row['n'] ?? 0;
            } else {
                $stats[$key] = 0; // Table might not exist yet
            }
        }

        return $stats;
    }

    // ── Private helper ────────────────────────────────────────────────────────
    private function sanitise($input) {
        return htmlspecialchars(strip_tags(trim($input)));
    }
}
?>
