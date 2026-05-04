# TO-DO — Pastimes Project Checklist

## ✅ COMPLETED — Core Requirements Met

### Database & Structure
- [x] Database `ClothingStore` exists
- [x] 4 tables: `tblUser`, `tblAdmin`, `tblClothes`, `tblAorder`
- [x] Primary keys set on all tables
- [x] Foreign keys linking tables
- [x] `DBConn.php` — working MySQLi connection
- [x] `createTable.php` — resets tblUser from userData.txt
- [x] `loadClothingStore.php` — full reset, 30 rows per table

### Data Files
- [x] `database/userData.txt` — seed data
- [x] `database/adminData.txt` — seed data  
- [x] `database/clothesData.txt` — seed data
- [x] `database/orderData.txt` — seed data
- [x] `database/myClothingStore.sql` — full DB export

### User System
- [x] `register.php` — all fields required, password ≥ 8, `password_hash()`, saves as `pending`
- [x] `login.php` — `password_verify()`, checks `verified` status, sticky form, error handling
- [x] `dashboard.php` — shows "User X is logged in", associative array display, item listings
- [x] `settings.php` — My Account section moved here (username, email, phone, status, delivery address)
- [x] `viewItem.php` — item detail page with placeholder images
- [x] `delivery.php` — delivery address form (residential/work)
- [x] `logout.php` — session destroy

### Admin System
- [x] `adminLogin.php` — separate admin login using `tblAdmin`
- [x] `adminPanel.php` — view pending users, approve/reject
- [x] `manageCustomers.php` — full CRUD (add, edit, delete users)

### Frontend & Code Quality
- [x] Clean UI with consistent styling (`css/style.css`)
- [x] OOP classes (`classes/UserAuth.php`, `classes/Database.php`)
- [x] Student declarations in all PHP files
- [x] Proper folder structure (`/css`, `/js`, `/images`, `/database`, `/classes`)

---

## ⚠️ PENDING — Still Needs Work

### Phase A: Critical Missing Features (Required for Brief)

#### 1. Cart & Ordering System ⭐ HIGH PRIORITY
**Current State:** Items display but no functional cart  
**What the brief requires:**
- [ ] "Add to Cart" button that actually saves to cart
- [ ] Cart page showing selected items
- [ ] Popup showing SellPrice when adding item
- [ ] Return to item list from popup
- [ ] Orders save to `tblAorder` with buyerID, clothesID, totalPrice

**Files to create/modify:**
- `cart.php` — new page showing cart items
- `addToCart.php` — backend handler (AJAX or form post)
- `dashboard.php` — replace "View Item" button with functional "Add to Cart"
- `viewItem.php` — add functional "Add to Cart" button

#### 2. Demo Images ⭐ HIGH PRIORITY
**Current State:** `images/` folder is **empty** (0 items)  
**What the brief requires:** At least 5 .jpg images in images folder

**Action Items:**
- [ ] Add minimum 5 product images to `images/`
- [ ] Use naming scheme: `item-01-classic-white-shirt.jpg` etc.
- [ ] See `IMAGE_GUIDE.md` for full list of 30 recommended images

#### 3. Word Document ⭐ HIGH PRIORITY
**Current State:** Not present in project  
**What the brief requires:** Word doc with table structures

**Action Items:**
- [ ] Create Word document
- [ ] Include table structures for all 4 tables:
  - Column names
  - Data types + sizes
  - Constraints (NOT NULL, etc.)
  - Keys (PK, FK)
- [ ] Add screenshots from phpMyAdmin showing table structure tabs
- [ ] Include student details + declaration

---

### Phase B: Nice-to-Have Improvements

#### 4. Item Images Auto-Detection
**Current State:** Placeholder images work, but local image detection ready  
**Improvement:**
- [ ] Add 5–10 real product images to `images/` folder
- [ ] Test that `getItemImage()` helper correctly finds local images

#### 5. Cart Enhancements
- [ ] Show cart count badge in navigation
- [ ] Remove item from cart
- [ ] Update quantities in cart
- [ ] Checkout flow (saves to `tblAorder`)

#### 6. Search & Filter
- [ ] Search bar on dashboard to find items
- [ ] Filter by category (tops, bottoms, etc.)
- [ ] Filter by size
- [ ] Filter by price range

#### 7. Item Condition Display
- [ ] Show actual `itemCondition` from database (currently hardcoded as "Good")
- [ ] Different condition badges (Like New, Very Good, Good, Fair)

---

## 📋 Quick Priority List

### Do This Week (Required for Submission)
| Priority | Task | File(s) | Est. Time |
|----------|------|---------|-----------|
| 1 | Add 5+ images to `images/` folder | `/images/` | 30 min |
| 2 | Create Word document with table structures | `.docx` file | 1 hour |
| 3 | Build cart system (add to cart, view cart) | `cart.php`, `addToCart.php` | 2–3 hours |
| 4 | Add popup with price when adding to cart | `dashboard.php`, JS | 1 hour |
| 5 | Test full flow end-to-end | All pages | 30 min |

### Demo Video Flow to Record
1. Register new user → see "pending" message
2. Try login → blocked, sticky form works
3. Admin login → approve user
4. User login → "User X is logged in" + data table
5. Browse items → images load
6. Add to cart → popup with price → back to items
7. Admin CRUD → add/edit/delete user
8. Show code: `password_hash()`, `password_verify()`, SQL queries

---

## 🎯 Definition of Done

This project is **submission-ready** when:
- [x] All Phase A items above are checked off
- [ ] 5+ images in `images/` folder
- [ ] Word document created with table structures
- [ ] Cart system functional (add items, view cart, popup)
- [ ] Demo video recorded showing full flow
- [ ] No PHP errors on any page
- [ ] `loadClothingStore.php` runs successfully (30 rows each table)
