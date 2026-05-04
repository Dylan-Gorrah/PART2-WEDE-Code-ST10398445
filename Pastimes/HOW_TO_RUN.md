# How to Run the Pastimes Project — Step by Step

Hey! This guide will walk you through getting the Pastimes website up and running on your computer. Follow the steps in order and you should be good to go.

---

## What You Need Before You Start

Make sure **XAMPP** is installed on your computer. If you don't have it yet, download and install it first. You'll need Apache and MySQL running.

---

## Step 1: Start Your Web Server

1. Open your **XAMPP Control Panel** (you can find it in your Start Menu).
2. Click the **Start** button next to **Apache**.
3. Click the **Start** button next to **MySQL**.
4. Wait until both turn green. If they don't start, check that nothing else is using port 80.

**What this does:** It turns your computer into a mini web server so you can run PHP files.

---

## Step 2: Create the Database

1. Open your web browser (Chrome, Firefox, whatever you use).
2. In the address bar at the top, type:
   ```
   http://localhost/phpmyadmin
   ```
   and press Enter.
3. You should see the phpMyAdmin dashboard. On the left side, click **New**.
4. In the box that says **Database name**, type exactly:
   ```
   ClothingStore
   ```
5. Next to **Collation**, pick `utf8_general_ci` from the dropdown.
6. Click the **Create** button.
7. That's it — your database is ready. You don't need to create any tables by hand.

**What this does:** It creates an empty box called `ClothingStore` where all your website data will live.

---

## Step 3: Put the Project in the Right Place

1. Find the `Pastimes` folder on your Desktop.
2. Copy the **entire** `Pastimes` folder.
3. Navigate to your XAMPP `htdocs` folder. It's usually at:
   ```
   C:\xampp\htdocs\
   ```
4. Paste the `Pastimes` folder inside `htdocs`.

Your folder should now look like this:
```
C:\xampp\htdocs\pastimes\
    ├── index.php
    ├── login.php
    ├── register.php
    ├── classes/
    ├── css/
    ├── js/
    ├── database/
    └── ... (other files)
```

**What this does:** It puts your website files where the web server can find and serve them.

---

## Step 4: Load the Database with Sample Data

1. Open your browser again.
2. Go to this address:
   ```
   http://localhost/pastimes/loadClothingStore.php
   ```
3. You should see a dark page with green checkmarks saying tables were created and rows were inserted.
4. Make sure you see:
   - `tblUser — inserted 30 / 30 rows`
   - `tblAdmin — inserted 30 / 30 rows`
   - `tblClothes — inserted 30 / 30 rows`
   - `tblAorder — inserted 30 / 30 rows`

If you see red X's instead, something went wrong. Check that your database name is exactly `ClothingStore` and that Apache and MySQL are running.

**What this does:** It builds all the tables and fills them with fake users, admins, clothes, and orders so you have something to test with.

---

## Step 5: Register a New User

1. Go to:
   ```
   http://localhost/pastimes/register.php
   ```
2. Fill in all the fields:
   - First Name, Last Name
   - Email Address
   - Username
   - Phone Number
   - Password (at least 8 characters)
   - Confirm Password (same as above)
3. Click **Create Account**.
4. You should see a green message saying your registration was successful and your account is pending admin verification.

**Important:** You won't be able to log in yet — an admin has to approve you first.

---

## Step 6: Log In As an Admin and Approve the User

1. Go to:
   ```
   http://localhost/pastimes/adminLogin.php
   ```
2. Log in with these default admin credentials:
   - **Username:** `superadmin`
   - **Password:** `Admin1234!`
3. Click **Admin Sign In**.
4. You should see the Admin Dashboard with stats and a list of pending registrations.
5. Find your newly registered user in the **Pending Registrations** table.
6. Click the **Approve** button next to their name.
7. The page will reload and you'll see a confirmation message.

**What this does:** The admin flips the user's status from `pending` to `verified`, which lets them log in.

---

## Step 7: Log In As the Approved User

1. Go to:
   ```
   http://localhost/pastimes/login.php
   ```
2. Enter:
   - **Username:** the username you registered with
   - **Email:** the email you registered with
   - **Password:** your password
3. Click **Sign In**.
4. You should see:
   - A welcome banner saying: **"User [Your Name] is logged in"**
   - A table showing your account details
   - A **Go to Dashboard** button

Click **Go to Dashboard** to browse the clothing listings.

---

## Step 8: Try the Delivery Address Page

1. From the dashboard, click **Update Delivery Address** (or go directly to `http://localhost/pastimes/delivery.php`).
2. Pick **Residential** or **Work**.
3. Fill in your street address, suburb, city, province, and postal code.
4. Click **Save Address**.
5. Go back to the dashboard — your address should now show up.

---

## Step 9: Log Out

1. Click the **Sign Out** button (top right of the dashboard, or go to `http://localhost/pastimes/logout.php`).
2. You'll be sent back to the login page.

---

## If Something Goes Wrong

| Problem | What to Check |
|---------|--------------|
| "Database Connection Failed" | Make sure the `ClothingStore` database exists in phpMyAdmin and that MySQL is running |
| "Page not found" 404 | Make sure the `Pastimes` folder is inside `C:\xampp\htdocs\` and that Apache is running |
| "Account pending" when trying to log in | Go to adminLogin.php and approve the user first |
| Tables aren't created | Run `loadClothingStore.php` again and check for red error messages |
| Wrong password on admin login | Make sure you ran `loadClothingStore.php` at least once to seed the admin data |

---

## Quick Reference — Useful Links

| Page | URL |
|------|-----|
| Home (redirects to login or dashboard) | `http://localhost/pastimes/` |
| User Login | `http://localhost/pastimes/login.php` |
| Register | `http://localhost/pastimes/register.php` |
| Admin Login | `http://localhost/pastimes/adminLogin.php` |
| Admin Dashboard | `http://localhost/pastimes/adminPanel.php` |
| User Dashboard | `http://localhost/pastimes/dashboard.php` |
| Delivery Address | `http://localhost/pastimes/delivery.php` |
| Reset All Database Tables | `http://localhost/pastimes/loadClothingStore.php` |

---

*That's it — you're all set! If you need help, ask your team or lecturer.*
