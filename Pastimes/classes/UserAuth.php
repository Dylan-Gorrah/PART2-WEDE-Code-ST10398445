<?php
/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : classes/UserAuth.php
 * Description    : Object-Oriented class that handles all user authentication logic
 *                  including registration, login, session management and logout.
 */

class UserAuth {
    // ── Property: the MySQLi connection from DBConn.php ──────────────────────
    private $conn;

    // ── Constructor: receives the $conn variable from DBConn.php ─────────────
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ════════════════════════════════════════════════════════════════════════
    //  REGISTER — validate and save a new user to tblUser
    //  Returns: array ['success' => bool, 'message' => string]
    // ════════════════════════════════════════════════════════════════════════
    public function register($data) {
        // Step 1: Pull all the fields out of the submitted data
        $firstName  = $this->sanitise($data['firstName'] ?? '');
        $lastName   = $this->sanitise($data['lastName']  ?? '');
        $email      = $this->sanitise($data['email']     ?? '');
        $username   = $this->sanitise($data['username']  ?? '');
        $phone      = $this->sanitise($data['phone']     ?? '');
        $password   = $data['password']        ?? '';   // NOT sanitised before hashing
        $confirmPwd = $data['confirmPassword'] ?? '';

        // Step 2: Server-side validation (HTML5 can be bypassed, PHP cannot)
        $errors = [];

        if (empty($firstName))   $errors[] = "First name is required.";
        if (empty($lastName))    $errors[] = "Last name is required.";
        if (empty($email))       $errors[] = "Email address is required.";
        if (empty($username))    $errors[] = "Username is required.";
        if (empty($phone))       $errors[] = "Phone number is required.";
        if (empty($password))    $errors[] = "Password is required.";

        // Brief requirement: 8-character minimum password
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }

        // Passwords must match
        if ($password !== $confirmPwd) {
            $errors[] = "Passwords do not match.";
        }

        // Basic email format check
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }

        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(' ', $errors)];
        }

        // Step 3: Check for duplicate email or username
        $stmt = $this->conn->prepare("SELECT userID FROM tblUser WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => "An account with that email or username already exists."];
        }
        $stmt->close();

        // Step 4: Hash the password — NEVER store plain text
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Step 5: Insert the new user with status = 'pending'
        // They can't log in until an admin changes status to 'verified'
        $stmt = $this->conn->prepare(
            "INSERT INTO tblUser (firstName, lastName, email, username, password, phone, status, createdAt)
             VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())"
        );
        $stmt->bind_param("ssssss", $firstName, $lastName, $email, $username, $hashedPassword, $phone);

        if ($stmt->execute()) {
            $stmt->close();
            return [
                'success' => true,
                'message' => "Registration successful! Your account is pending admin verification. You will be able to log in once approved."
            ];
        }

        $stmt->close();
        return ['success' => false, 'message' => "Registration failed. Please try again. (" . $this->conn->error . ")"];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  LOGIN — verify credentials and start a session
    //  Returns: array ['success' => bool, 'message' => string, 'user' => array|null]
    // ════════════════════════════════════════════════════════════════════════
    public function login($username, $email, $password) {
        // Step 1: Basic input validation
        if (empty($username) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => "All fields are required.", 'user' => null];
        }

        // Step 2: Look up user by username AND email (both must match)
        $stmt = $this->conn->prepare(
            "SELECT userID, firstName, lastName, email, username, password, phone, status, deliveryAddress, createdAt
             FROM tblUser
             WHERE username = ? AND email = ?"
        );
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => "No account found with that username and email combination.", 'user' => null];
        }

        // Step 3: Fetch the user row using associative names (as required by brief)
        $user = $result->fetch_assoc();
        $stmt->close();

        // Step 4: Check account status — must be 'verified' by admin
        if ($user['status'] === 'pending') {
            return [
                'success' => false,
                'message' => "Your account is pending admin verification. Please wait for approval before logging in.",
                'user'    => null
            ];
        }

        if ($user['status'] === 'rejected') {
            return [
                'success' => false,
                'message' => "Your account registration was not approved. Please contact Pastimes support.",
                'user'    => null
            ];
        }

        // Step 5: Compare the entered password against the stored bcrypt hash
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => "Incorrect password. Please try again.", 'user' => null];
        }

        // Step 6: All good — start the session and store user data
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['userID']   = $user['userID'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name']     = $user['firstName'] . ' ' . $user['lastName'];
        $_SESSION['email']    = $user['email'];
        $_SESSION['loggedIn'] = true;

        return [
            'success' => true,
            'message' => "Welcome back, " . htmlspecialchars($user['firstName']) . "!",
            'user'    => $user   // Pass full user data back for displaying on screen
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    //  IS LOGGED IN — check if a valid user session exists
    // ════════════════════════════════════════════════════════════════════════
    public function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true && isset($_SESSION['userID']);
    }

    // ════════════════════════════════════════════════════════════════════════
    //  REQUIRE LOGIN — redirect to login.php if not logged in
    //  Call this at the top of any protected page
    // ════════════════════════════════════════════════════════════════════════
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header("Location: login.php?reason=session_expired");
            exit();
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    //  LOGOUT — destroy the session and redirect
    // ════════════════════════════════════════════════════════════════════════
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Clear all session variables
        $_SESSION = [];
        // Destroy the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    // ════════════════════════════════════════════════════════════════════════
    //  GET USER BY ID — fetch full user row from DB (used on dashboard)
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
    //  SANITISE — clean user input (trim whitespace, remove HTML tags)
    //  Note: Always use prepared statements for DB queries — this is for display
    // ════════════════════════════════════════════════════════════════════════
    private function sanitise($input) {
        return htmlspecialchars(strip_tags(trim($input)));
    }
}
?>
