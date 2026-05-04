# Pastimes — PHP UI/UX Redesign Guide
> Bridging the gap between `StoreDemo.html` and the PHP pages

---

## 1. The StoreDemo Design Language (What You're Targeting)

Before touching any PHP file, understand the visual DNA of the demo. Everything flows from these decisions:

### Colour Palette
```
--ivory:         #FAF6EF   ← page background, warm off-white
--ivory-dark:    #F0E8D8   ← dividers, subtle separators
--espresso:      #1C130B   ← primary text, headings
--gold:          #BF8B30   ← primary action colour (buttons, active states)
--gold-light:    #D4A855   ← hover state for gold
--gold-pale:     #F2E0B6   ← selected/active card backgrounds
--stone:         #9A8B7A   ← muted/secondary text
--stone-pale:    #EBE4D8   ← empty states, disabled inputs
--cream-card:    #FDF9F3   ← card surface
--sage:          #6B7C60   ← optional accent (not used heavily)
```

### Typography
- **Headings / display text** → `Cormorant Garamond` (serif, lightweight, editorial luxury feel)
- **Body / UI text** → `Jost` (geometric sans, clean, modern)
- Headings use `font-weight: 300–400` (thin/regular) — NOT bold. This is intentional. The luxury feel comes from *lightness*, not weight.

### The "Feel" in One Sentence
> Warm luxury pre-loved fashion app — like a high-end thrift boutique's mobile app. Think Vestiaire Collective meets a South African township market. Earthy tones, gold accents, editorial typography.

### Layout Rules
- Fixed top nav (`64px` tall, frosted glass: `backdrop-filter: blur(12px)`)
- **Fixed bottom nav** (`72px` tall) — this is the PRIMARY navigation in the demo. The PHP pages currently don't have this at all.
- Page content has `padding-top: 64px` and `padding-bottom: 84px` to clear both navbars
- Cards use `border-radius: 2px` on buttons (sharp, intentional) but `border-radius: var(--radius)` on cards (slightly rounded)
- `fadeInUp` animation on every page: `opacity 0 → 1` + `translateY(16px) → 0` over `0.4s`

---

## 2. Page-by-Page Analysis & Improvements

---

### `login.php` — Sign In Page

#### Current State
The current PHP page uses a centred `.auth-card` (a white card floating on the ivory background). It works, but it's flat and doesn't match the richness of the demo.

#### What the Demo Does Differently
The demo's auth page (`#page-login`) has a **split layout**:

```
┌──────────────────────────────┐
│   AUTH HERO IMAGE            │  ← 280px photo with dark overlay
│   PASTIMES logo over photo   │  ← serif logo + tagline on top of image
├──────────────────────────────┤
│   Tab switcher               │  ← Sign In / Create Account pills
│   Form fields                │
│   Social login button        │
└──────────────────────────────┘
```

The hero image creates immediate brand context. The form sits below as a natural continuation, not an isolated card.

#### PHP Improvements Needed

**1. Add the `auth-hero` image section above the form:**
```php
<!-- In login.php, replace the plain auth-card wrapper with this structure -->
<main class="auth-page">

  <!-- Hero image header (new) -->
  <div class="auth-hero">
    <img src="img/auth-hero.jpg" alt="Pre-loved fashion"/>
    <div class="auth-hero-overlay"></div>
    <div class="auth-hero-content">
      <div class="auth-logo">PASTIMES</div>
      <div class="auth-tagline">Pre-Loved Fashion · South Africa</div>
    </div>
  </div>

  <!-- Form body (rename auth-card to auth-body) -->
  <div class="auth-body">
    <!-- tab switcher, form, etc -->
  </div>

</main>
```

**2. Add these CSS classes to `style.css`:**
```css
.auth-hero {
  position: relative;
  height: 280px;
  overflow: hidden;
  background: #2A1F14;
}
.auth-hero img {
  width: 100%; height: 100%;
  object-fit: cover; object-position: top center;
  opacity: 0.82;
}
.auth-hero-overlay {
  position: absolute; inset: 0;
  background: rgba(28,19,11,0.5);
}
.auth-hero-content {
  position: absolute;
  bottom: 28px; left: 24px;
}
.auth-body {
  padding: 24px 20px 40px;
  background: var(--ivory);
}
/* Remove the old floating card style for auth pages */
.auth-page .auth-card {
  box-shadow: none;
  border: none;
  background: transparent;
  padding: 0;
  border-radius: 0;
}
```

**3. The tab switcher needs pill/toggle styling:**
```css
.tab-switcher {
  display: flex;
  background: var(--ivory-dark);
  border-radius: 2px;
  padding: 3px;
  margin-bottom: 24px;
}
.tab-btn {
  flex: 1;
  padding: 9px 12px;
  border: none;
  border-radius: 2px;
  background: transparent;
  font-family: var(--sans);
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  cursor: pointer;
  color: var(--stone);
  transition: all 0.2s;
}
.tab-btn.active {
  background: var(--espresso);
  color: #fff;
}
```

---

### `register.php` — Create Account Page

#### Current State
Shares the same `auth-card` wrapper as login. The form itself is good (sticky fields, password strength bar), but visually it's the same flat card.

#### What the Demo Does Differently
Same `auth-hero` + `auth-body` split as login. The key demo extra is the **"I want to"** role selector — three pill buttons: `Buy`, `Sell`, `Both`. This is a UX improvement that doesn't exist in the PHP yet.

#### PHP Improvements Needed

**1. Apply the same `auth-hero` + `auth-body` split from login.php above.**

**2. Add the role-selector pill group after the phone field:**
```php
<!-- After the phone input in register.php -->
<div class="form-group">
  <label class="form-label">I want to *</label>
  <div class="filter-pills" style="display:flex; gap:8px;">
    <button type="button" class="pill active" data-role="buy"
            onclick="setRole(this,'buy')">Buy</button>
    <button type="button" class="pill" data-role="sell"
            onclick="setRole(this,'sell')">Sell</button>
    <button type="button" class="pill" data-role="both"
            onclick="setRole(this,'both')">Both</button>
  </div>
  <!-- Hidden input carries the value to PHP -->
  <input type="hidden" name="userRole" id="userRole" value="buy"/>
</div>
```

```js
// In main.js or inline
function setRole(el, role) {
  document.querySelectorAll('[data-role]').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('userRole').value = role;
}
```

**3. Pill CSS (add to style.css):**
```css
.pill {
  flex: 1;
  padding: 10px 16px;
  border: 1.5px solid var(--stone-pale);
  border-radius: 2px;
  background: transparent;
  font-family: var(--sans);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  cursor: pointer;
  color: var(--stone);
  transition: all 0.2s;
}
.pill.active {
  border-color: var(--gold);
  background: var(--gold-pale);
  color: var(--espresso);
}
```

---

### `dashboard.php` — Main Buyer Dashboard

#### Current State
Good card grid structure. The product grid is there. The biggest gap: **no bottom navigation bar** and the product cards are very plain (they use an SVG placeholder with no image at all, and no wishlist/heart button).

#### What the Demo Does Differently
The demo's home page is much richer:

- **Hero banner** at the top of the feed with a full-bleed image
- **Category chip filter strip** (All, Tops, Bottoms, Dresses, Outerwear…) — horizontal scroll
- **Product cards** have: thumbnail image, wishlist heart button (top-right), brand label, title, size, seller name, price, condition badge
- **FAB (Floating Action Button)** — gold `+` button bottom-right for "Sell an item"
- **Bottom nav** with 5 tabs: Home, Browse, Dashboard, Messages, Tracking

#### PHP Improvements Needed

**1. Add the bottom navigation to dashboard.php (and all authenticated pages):**
```php
<!-- Paste this right before </body> on every logged-in page -->
<nav class="bottom-nav">
  <a href="dashboard.php" class="bnav-item <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
    <svg viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    Home
  </a>
  <a href="browse.php" class="bnav-item <?= basename($_SERVER['PHP_SELF']) === 'browse.php' ? 'active' : '' ?>">
    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    Browse
  </a>
  <a href="delivery.php" class="bnav-item <?= basename($_SERVER['PHP_SELF']) === 'delivery.php' ? 'active' : '' ?>">
    <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
    Address
  </a>
  <a href="logout.php" class="bnav-item">
    <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    Sign Out
  </a>
</nav>
```

**2. Bottom nav CSS (add to style.css):**
```css
.bottom-nav {
  position: fixed; bottom: 0; left: 0; right: 0; z-index: 100;
  height: 72px;
  background: rgba(250,246,239,0.97);
  backdrop-filter: blur(12px);
  border-top: 1px solid rgba(191,139,48,0.18);
  display: flex; align-items: center;
  padding-bottom: env(safe-area-inset-bottom);
}
.bnav-item {
  flex: 1;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  gap: 4px; height: 100%;
  text-decoration: none;
  font-family: var(--sans); font-size: 10px;
  font-weight: 500; letter-spacing: 0.06em;
  color: var(--stone); text-transform: uppercase;
  transition: color 0.2s;
}
.bnav-item.active { color: var(--gold); }
.bnav-item svg {
  width: 22px; height: 22px;
  stroke: currentColor; fill: none;
  stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round;
}
.bnav-item.active svg { stroke: var(--gold); }
/* Adjust body padding so content doesn't hide behind bottom nav */
.page-wrap { padding-bottom: 84px !important; }
```

**3. Update product cards to match the demo:**
The demo cards have a heart/wishlist button in the top-right corner of the image area. In PHP this can be a form button or JS-only toggle for now.

```php
<!-- Replace the current product-card inner HTML with this -->
<div class="product-card">
  <div class="product-img" style="position:relative;">
    <!-- If you have images: <img src="..." alt="..."> -->
    <!-- Placeholder: -->
    <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="1" opacity="0.3">
      <path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46..."/>
    </svg>
    <!-- Wishlist button (top right of image) -->
    <button class="wishlist-btn" data-id="<?= $item['clothesID'] ?>">
      <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
    </button>
    <!-- Condition badge -->
    <span class="condition-badge">Good</span>
  </div>
  <!-- rest of card body unchanged -->
</div>
```

```css
.wishlist-btn {
  position: absolute; top: 10px; right: 10px;
  width: 34px; height: 34px;
  background: rgba(250,246,239,0.88);
  border: none; border-radius: 50%; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  backdrop-filter: blur(4px);
  transition: background 0.2s;
}
.wishlist-btn svg {
  width: 16px; height: 16px;
  stroke: var(--stone); fill: none; stroke-width: 1.5;
}
.wishlist-btn.liked svg { stroke: #E85D5D; fill: #E85D5D; }

.condition-badge {
  position: absolute; bottom: 10px; left: 10px;
  font-size: 9px; font-weight: 700;
  letter-spacing: 0.14em; text-transform: uppercase;
  color: var(--espresso-light);
  background: var(--gold-pale);
  padding: 3px 8px;
  border-radius: 2px;
}
```

**4. Add a category chip filter above the product grid:**
```php
<!-- Above the product grid in dashboard.php -->
<div style="overflow-x:auto; -webkit-overflow-scrolling:touch; padding: 0 0 8px;">
  <div class="filter-pills" style="display:flex; gap:8px; width:max-content;">
    <button class="cat-chip active" onclick="filterCat(this,'all')">All</button>
    <button class="cat-chip" onclick="filterCat(this,'tops')">Tops</button>
    <button class="cat-chip" onclick="filterCat(this,'bottoms')">Bottoms</button>
    <button class="cat-chip" onclick="filterCat(this,'dresses')">Dresses</button>
    <button class="cat-chip" onclick="filterCat(this,'outerwear')">Outerwear</button>
    <button class="cat-chip" onclick="filterCat(this,'shoes')">Shoes</button>
    <button class="cat-chip" onclick="filterCat(this,'accessories')">Accessories</button>
  </div>
</div>
```
```css
.cat-chip {
  padding: 8px 16px;
  border: 1.5px solid var(--stone-pale);
  border-radius: 20px; /* pill shape */
  background: transparent;
  font-family: var(--sans);
  font-size: 12px; font-weight: 600;
  letter-spacing: 0.08em; text-transform: uppercase;
  cursor: pointer; white-space: nowrap;
  color: var(--stone); transition: all 0.2s;
}
.cat-chip.active {
  border-color: var(--gold);
  background: var(--gold);
  color: #fff;
}
```

---

### `delivery.php` — Delivery Address Page

#### Current State
This is actually one of the better PHP pages — the address type toggle buttons (Residential / Work) already match the demo's card-selection pattern. The structure is clean.

#### What the Demo Does Differently
The demo's delivery/tracking section uses a **step timeline** to show order progress. For the address *form* specifically, the demo uses the same pattern your PHP already has: card-style toggle buttons with icon + label.

#### PHP Improvements Needed

**1. Minor: Add the back-arrow in the top nav (already done — good!) but make it consistent across all inner pages.**

**2. The "Currently Saved Address" preview card style is too plain.** Replace the monospace font display with a formatted address card:

```php
<!-- Replace the current saved address card with this -->
<?php if (!empty($user['deliveryAddress'])): ?>
<div class="card mt-24" style="border-left: 3px solid var(--gold);">
  <div style="display:flex; justify-content:space-between; align-items:flex-start;">
    <div>
      <div style="font-size:10px;font-weight:700;letter-spacing:0.14em;
                  text-transform:uppercase;color:var(--stone);margin-bottom:8px;">
        📍 Saved Address
      </div>
      <div style="font-size:14px;color:var(--espresso);line-height:1.9;">
        <?php
          // Parse and display cleanly (not in monospace)
          $lines = explode("\n", $user['deliveryAddress']);
          $fields = [];
          foreach ($lines as $line) {
            [$key, $val] = array_pad(explode(':', $line, 2), 2, '');
            $fields[trim($key)] = trim($val);
          }
          echo htmlspecialchars($fields['STREET'] ?? '');
          echo '<br>';
          echo htmlspecialchars(($fields['SUBURB'] ?? '') . ', ' . ($fields['CITY'] ?? ''));
          echo '<br>';
          echo htmlspecialchars(($fields['PROVINCE'] ?? '') . ' · ' . ($fields['CODE'] ?? ''));
        ?>
      </div>
    </div>
    <span class="badge" style="background:var(--gold-pale);color:var(--espresso-light);">
      <?= htmlspecialchars($fields['TYPE'] ?? 'Residential') ?>
    </span>
  </div>
</div>
<?php endif; ?>
```

**3. The page needs the bottom nav from dashboard.php added (same pattern for all authenticated pages).**

---

### `adminLogin.php` — Admin Sign In

#### Current State
Very similar to `login.php` — the `auth-card` centred card. It correctly has the restricted access messaging and the warning banner.

#### What the Demo Does Differently
The demo doesn't have a separate admin login, but the same `auth-hero` pattern should apply. For admin pages, use a **darker, more serious** tone — swap the warm fashion hero image for a darker or abstract image, and change the tagline.

#### PHP Improvements Needed

**1. Apply the same `auth-hero` + `auth-body` split.**

**2. Use a visually distinct hero to signal "this is different from user login":**
```php
<div class="auth-hero" style="background: #0F0A06;">
  <!-- Dark overlay only — no photo, more "restricted" feel -->
  <div style="position:absolute;inset:0;
              background: repeating-linear-gradient(
                45deg, rgba(191,139,48,0.03) 0, rgba(191,139,48,0.03) 1px,
                transparent 0, transparent 50%);
              background-size: 12px 12px;">
  </div>
  <div class="auth-hero-content">
    <div class="auth-logo" style="color:#fff;">PASTIMES</div>
    <div style="font-size:10px;font-weight:600;letter-spacing:0.2em;
                text-transform:uppercase;color:var(--gold-light);margin-top:6px;">
      🔒 Admin · Restricted Access
    </div>
  </div>
</div>
```

**3. Remove the default credentials hint from production** (or gate it behind a PHP constant `APP_DEBUG`):
```php
<?php if (defined('APP_DEBUG') && APP_DEBUG): ?>
  <p class="text-center mt-16" style="font-size:11px;color:var(--stone);">
    Default: <strong>superadmin</strong> / <strong>Admin1234!</strong>
  </p>
<?php endif; ?>
```

---

## 3. Global Changes Needed (Apply to All Pages)

These affect `style.css` and `main.js` — not page-specific.

### A. Page Entrance Animation
Every `.page` in the demo fades up on load. Add this to every `<body>` in PHP by wrapping content in a div:

```css
/* In style.css */
.page-wrap {
  animation: fadeInUp 0.4s ease forwards;
}
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}
```

### B. Top Nav: Logo Should Be Centred
The demo has: `[icon]  PASTIMES (centred)  [icon]`
The current PHP nav has the logo left-aligned. Fix:

```css
.top-nav {
  display: flex;
  align-items: center;
  position: fixed; top: 0; left: 0; right: 0;
  height: 64px; z-index: 100;
  background: rgba(250,246,239,0.96);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(191,139,48,0.18);
  padding: 0 16px; gap: 8px;
}
.nav-logo {
  font-family: var(--serif);
  font-size: 24px; font-weight: 500;
  letter-spacing: 0.14em;
  color: var(--espresso);
  text-decoration: none;
  flex: 1;              /* takes remaining space */
  text-align: center;   /* centres the text in that space */
}
```

### C. Buttons: Keep `border-radius: 2px` (Not Rounded)
The demo uses sharp `2px` radius on all buttons — this is part of the luxury editorial feel. Rounded/pill buttons would break the aesthetic. The `btn-primary` and `btn-outline` should both use `border-radius: 2px`.

### D. Form Inputs: Square Corners + Gold Focus Ring
```css
.form-input, .form-select, .form-textarea {
  border-radius: 2px;
  border: 1.5px solid var(--stone-pale);
  height: 48px;
  padding: 0 16px;
  font-family: var(--sans);
  font-size: 14px;
  background: var(--cream-card);
  color: var(--espresso);
  width: 100%;
  transition: border-color 0.2s, box-shadow 0.2s;
  outline: none;
}
.form-input:focus, .form-select:focus, .form-textarea:focus {
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(191,139,48,0.12);
}
```

### E. Alert / Flash Messages
Match the demo's alert styling — left-border accent, no harsh red backgrounds:

```css
.alert {
  padding: 14px 16px;
  border-radius: 2px;
  font-size: 13px;
  line-height: 1.5;
  margin-bottom: 16px;
  border-left: 3px solid transparent;
}
.alert-error   { background: #FEF2F2; color: #991B1B; border-left-color: #EF4444; }
.alert-success { background: #F0FDF4; color: #166534; border-left-color: #22C55E; }
.alert-warning { background: #FFFBEB; color: #92400E; border-left-color: var(--gold); }
.alert-info    { background: var(--sage-pale); color: var(--sage); border-left-color: var(--sage); }
```

---

## 4. UX Flow Improvements (Not Just Visual)

These are behaviour changes that improve how users move through the app:

| Current Behaviour | Demo Pattern | How to Fix in PHP |
|---|---|---|
| After login success, stay on `login.php` showing a table | Immediately show the dashboard with a welcome banner | Add `header("Location: dashboard.php?welcome=1")` after successful login. Show the "User X is logged in" banner in `dashboard.php` via `$_GET['welcome']` |
| No way back from `delivery.php` except top-nav | Consistent back arrow in every inner page's top nav | Already done in `delivery.php` — replicate to any future inner pages |
| Register success shows a plain text message | Route to login page with a success banner | `header("Location: login.php?registered=1")` — then check `$_GET['registered']` in `login.php` to show the green banner |
| `dashboard.php` shows account info + products in same scroll | Demo separates these: home feed = products, dashboard tab = account | Consider splitting into `home.php` (product feed) and `dashboard.php` (account info only) |
| No category filtering on product grid | Category chips filter the grid | Add `?category=tops` query param to the PHP and filter the SQL `WHERE` clause |

---

## 5. Quick-Reference: Component Mapping

| Demo CSS Class | PHP Equivalent | Notes |
|---|---|---|
| `.auth-hero` | Not in PHP yet | Add to login, register, adminLogin |
| `.auth-body` | `.auth-card` | Rename + remove the card box-shadow |
| `.bottom-nav` + `.bnav-item` | Not in PHP yet | Add to every authenticated page |
| `.cat-chip` | Not in PHP yet | Add to dashboard product section |
| `.pill` | Partial (register has pills) | Standardise CSS |
| `.wishlist-btn` | Not in PHP yet | Add to product cards |
| `.condition-badge` | Not in PHP yet | Map to a `condition` DB column |
| `.btn-full` | `.btn.btn-full` | Already exists — just ensure `border-radius: 2px` |
| `.fadeInUp` | Not applied yet | Add via `.page-wrap` animation |
| `.top-nav` logo centred | Left-aligned in PHP | Fix with `flex:1; text-align:center` |

---

## 6. Suggested File Structure Addition

```
css/
  style.css          ← main stylesheet (your existing one)
  auth.css           ← (optional) auth-hero split styles
js/
  main.js            ← add: wishlist toggle, cat chip filter, pill role picker
img/
  auth-hero.jpg      ← fashion photo for login/register hero
  auth-admin.jpg     ← darker photo for admin login (optional)
```

---

> **Bottom line for the PHP developer:** The biggest visual gap is the missing **`auth-hero` image header** on auth pages, the missing **bottom navigation bar** on authenticated pages, and the missing **fadeInUp** entrance animation. Fix those three things first and the pages will feel ~80% closer to the demo. Everything else (wishlist hearts, category chips, address card polish) is enhancement on top.
