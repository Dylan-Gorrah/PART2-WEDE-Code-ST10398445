# Pastimes — Part 2 Developer Guide
## What We're Building & How It All Fits Together

---

## Before You Start — Folder Structure

Drop everything into your XAMPP/WAMP `htdocs` folder like this:

```
htdocs/
└── pastimes/
    ├── index.php               ← Landing page (redirects to login or dashboard)
    ├── login.php               ← User login
    ├── register.php            ← New user registration
    ├── logout.php              ← Kills the session, sends you back to login
    ├── delivery.php            ← User fills in their delivery address
    ├── dashboard.php           ← What a logged-in buyer sees
    ├── adminLogin.php          ← Separate login just for admins
    ├── adminPanel.php          ← Admin dashboard (approve users, manage stock)
    ├── manageCustomers.php     ← Admin CRUD — add/edit/delete customers
    ├── DBConn.php              ← The DB connection (included everywhere)
    ├── createTable.php         ← Nukes tblUser, rebuilds it from userData.txt
    ├── loadClothingStore.php   ← Nukes ALL tables, rebuilds with 30 rows each
    ├── css/
    │   └── style.css
    ├── js/
    │   └── main.js
    ├── images/
    │   └── (your product images go here)
    ├── classes/
    │   ├── Database.php        ← OOP DB wrapper
    │   └── UserAuth.php        ← OOP class handling register/login logic
    └── database/
        ├── userData.txt        ← 5+ fake users for tblUser
        ├── adminData.txt       ← 5+ fake admins for tblAdmin
        ├── clothesData.txt     ← 5+ fake listings for tblClothes
        ├── orderData.txt       ← 5+ fake orders for tblAorder
        └── myClothingStore.sql ← Full DB export from phpMyAdmin
```

---

## Phase 1 — Set Up Your Database (Do this FIRST, manually in phpMyAdmin)

**What you're doing:** Creating the ClothingStore database before any PHP touches it.

1. Open phpMyAdmin → http://localhost/phpmyadmin
2. Click "New" on the left, name it `ClothingStore`, set collation to `utf8_general_ci`, click Create
3. You DON'T need to create tables by hand — `loadClothingStore.php` does that for you
4. Just make sure the empty `ClothingStore` database exists

**The four tables your site uses:**

| Table | What it stores |
|-------|---------------|
| `tblUser` | Everyone who registers — buyers and sellers |
| `tblAdmin` | Admin accounts (separate from regular users) |
| `tblClothes` | Clothing listings uploaded for sale |
| `tblAorder` | Orders placed by buyers |

**Relationships:**
- `tblClothes.userID` → links back to `tblUser.userID` (who is selling it)
- `tblAorder.buyerID` → links back to `tblUser.userID` (who bought it)
- `tblAorder.clothesID` → links back to `tblClothes.clothesID` (what was bought)

---

## Phase 2 — The Connection File (DBConn.php)

**What you're doing:** Every PHP page that talks to the database needs this file included at the top.

**How it works:**
- Sets up a MySQLi connection to `ClothingStore`
- If the connection fails, it stops everything and tells you why
- Every other PHP file does `require_once 'DBConn.php';` at the top

**You might need to change:** If your MySQL password isn't blank (default for XAMPP is blank), open `DBConn.php` and change `DB_PASS` to your password.

---

## Phase 3 — OOP Classes (classes/ folder)

**What you're doing:** The brief requires Object-Oriented PHP, so we've got two classes.

**Database.php** — A clean wrapper around the MySQLi connection. You create one instance and call methods on it instead of writing raw SQL everywhere.

**UserAuth.php** — Handles all user auth logic:
- `register($data)` — validates input, hashes the password, saves to DB with status = 'pending'
- `login($username, $email, $password)` — looks up the user, checks status is 'verified', compares the password hash
- `isLoggedIn()` — checks if there's a valid session
- `logout()` — destroys the session

**Why OOP here?** Because the brief specifically asks for it, and having auth logic in a class means register.php and login.php just call methods instead of repeating the same code twice.

---

## Phase 4 — The Registration Page (register.php)

**What you're doing:** A new customer fills in their details and creates an account.

**Fields required:**
- First Name + Last Name
- Email Address
- Username
- Password (minimum 8 characters)
- Confirm Password
- Phone Number

**What happens behind the scenes:**
1. HTML5 `required` attributes catch empty fields before the form even submits
2. PHP double-checks everything server-side (because users can bypass HTML5 validation)
3. Password is checked to be at least 8 characters
4. Both password fields are compared — must match
5. Email + username checked for duplicates (can't register twice)
6. `password_hash($password, PASSWORD_DEFAULT)` turns the password into a bcrypt hash — NEVER store plain text passwords
7. User is saved to `tblUser` with `status = 'pending'` — they can't log in yet
8. A message shows: "Registration received! An admin will verify your account before you can log in."

**Sticky form behaviour:** If validation fails, the form re-displays with all fields already filled in (except password fields). Users shouldn't have to retype everything just because of one mistake.

---

## Phase 5 — The Login Page (login.php)

**What you're doing:** Verified users log in with their username + email + password.

**What happens behind the scenes:**
1. PHP looks up the user by username AND email (both must match a row in tblUser)
2. Checks `status = 'verified'` — if it's 'pending', they get a message to wait for admin approval
3. `password_verify($inputPassword, $storedHash)` compares the entered password against the bcrypt hash in the DB
4. On success: session variables set (`$_SESSION['userID']`, `$_SESSION['username']`, `$_SESSION['name']`), redirect to dashboard
5. On failure: sticky form — username and email fields re-populate, but password clears for security
6. "User John Doe is logged in" banner shown on dashboard

**Session management:** The session keeps the user "logged in" across pages. Every protected page starts with `session_start()` and checks `$_SESSION['userID']` — if it's not set, you get sent back to login.php.

---

## Phase 6 — createTable.php

**What you're doing:** A utility script that resets tblUser.

**Run it by visiting:** `http://localhost/pastimes/createTable.php`

**What it does:**
1. Connects to ClothingStore via `DBConn.php`
2. Drops `tblUser` if it exists (`DROP TABLE IF EXISTS tblUser`)
3. Creates a fresh `tblUser` with all the right columns
4. Opens `database/userData.txt` and reads it line by line
5. For each line, hashes the password with `password_hash()` and inserts the row
6. Shows you a success/fail message for each row

**Every time you run this script, tblUser gets wiped and reloaded.** Great for testing, just don't run it in production.

---

## Phase 7 — Admin Login + Panel (Session 2)

**What you're doing:** A completely separate login system just for admins.

**adminLogin.php:**
- Has its own login form (username + password only, no email needed for admins)
- Checks against `tblAdmin` (NOT `tblUser`)
- Sets `$_SESSION['adminID']` on success
- Redirects to `adminPanel.php`

**adminPanel.php:**
- Shows all users currently with `status = 'pending'` — these are waiting for approval
- Admin clicks "Approve" → sets that user's status to 'verified' → they can now log in
- Admin clicks "Reject" → optionally delete or mark as rejected
- Shows a table of ALL customers with options to Edit / Delete

**manageCustomers.php:**
- Full CRUD for customers — Add new user manually, Edit their details, Delete them

---

## Phase 8 — loadClothingStore.php (Session 2)

**What you're doing:** The big nuclear reset script. Your lecturer runs this to build your entire database from scratch.

**What it does:**
- Drops ALL four tables in the right order (orders first, then clothes, then users/admins — so foreign keys don't break things)
- Creates all four tables fresh
- Inserts 30 fake rows into EACH table using `password_hash()` for user passwords
- Reports success/failure for each operation

**Run it at:** `http://localhost/pastimes/loadClothingStore.php`

---

## Phase 9 — Dashboard + Delivery (Session 2)

**dashboard.php:** What a logged-in user sees — their orders, a welcome message, links to browse listings.

**delivery.php:** A form where the user enters or updates their delivery address (residential or work). Saved to a `deliveryAddress` field in `tblUser` or a separate delivery table.

---

## Common Mistakes to Avoid

- **Don't forget `session_start()`** at the very top of every page that uses sessions — before any HTML output
- **Every file needs your student number + name + declaration** in a comment at the top — non-negotiable
- **Test the sticky form** — fill in wrong info and make sure your username/email stays in the fields
- **Test with a pending user** — register, try to log in, get the "pending" message, then go to admin, approve them, then log in again successfully
- **The video is non-negotiable** — record it last when everything works

---

## Quick Test Checklist

```
[ ] Visit http://localhost/pastimes/loadClothingStore.php → all tables created, 30 rows each
[ ] Visit http://localhost/pastimes/register.php → fill in all fields → see "pending" message
[ ] Try to log in with that new account → see "waiting for admin approval" message
[ ] Visit http://localhost/pastimes/adminLogin.php → log in as admin
[ ] Find the pending user → click Approve
[ ] Go back to login.php → log in as that user → see "User [Name] is logged in" 
[ ] All registration fields show errors if left blank
[ ] Password under 8 chars shows an error
[ ] Wrong password shows sticky form with username/email still filled in
```

---

## What Goes in Your Word Document

For each table, write out:

| Column Name | Data Type | Size | Constraints | Key |
|------------|-----------|------|-------------|-----|
| userID | INT | - | NOT NULL, AUTO_INCREMENT | PRIMARY KEY |
| firstName | VARCHAR | 100 | NOT NULL | - |
| ... | ... | ... | ... | ... |

Do this for all four tables: tblUser, tblAdmin, tblClothes, tblAorder.

Add screenshots from phpMyAdmin showing the table structure tab.

---

*Pastimes PoE — Part 2 Build Guide*  
*Replace [YOUR STUDENT NUMBER] and [YOUR NAME] in every PHP file header*
