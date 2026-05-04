/*
 * Student 1      : ST10398445 — Dylan Gorrah
 * Student 2      : ST10452409 — Liyema Masala
 * Student 3      : ST10444003 — Makwatsa Mabizela
 * Declaration    : This code is my own work where not referenced.
 * File           : js/main.js
 * Description    : Client-side JavaScript for Pastimes.
 *                  Handles tab switching, form validation feedback,
 *                  password strength indicator and UI interactions.
 */

// ── Wait for the DOM to be fully loaded before running any JS ────────────────
document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Tab switcher (Sign In / Create Account on login.php) ────────────
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = this.dataset.tab;
            // Remove active from all tabs and panels
            tabBtns.forEach(function (b) { b.classList.remove('active'); });
            document.querySelectorAll('.tab-panel').forEach(function (p) {
                p.style.display = 'none';
            });
            // Activate the clicked one
            this.classList.add('active');
            const panel = document.getElementById('panel-' + target);
            if (panel) panel.style.display = 'block';
        });
    });

    // ── 2. Password strength indicator ──────────────────────────────────────
    const pwdInput     = document.getElementById('password');
    const strengthBar  = document.getElementById('pwd-strength-bar');
    const strengthText = document.getElementById('pwd-strength-text');

    if (pwdInput && strengthBar) {
        pwdInput.addEventListener('input', function () {
            const score = getPasswordStrength(this.value);
            updateStrengthUI(score);
        });
    }

    /**
     * Rate password strength 0-4 based on:
     * length, uppercase, lowercase, numbers, special chars
     */
    function getPasswordStrength(pwd) {
        if (pwd.length === 0) return 0;
        let score = 0;
        if (pwd.length >= 8)  score++;
        if (pwd.length >= 12) score++;
        if (/[A-Z]/.test(pwd)) score++;
        if (/[0-9]/.test(pwd)) score++;
        if (/[^A-Za-z0-9]/.test(pwd)) score++;
        return Math.min(score, 4);
    }

    function updateStrengthUI(score) {
        if (!strengthBar || !strengthText) return;
        const levels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
        const colors = ['', '#C0392B', '#E67E22', '#F1C40F', '#27AE60'];
        const widths = ['0%', '25%', '50%', '75%', '100%'];

        strengthBar.style.width           = widths[score];
        strengthBar.style.backgroundColor = colors[score];
        strengthText.textContent          = score > 0 ? levels[score] : '';
        strengthText.style.color          = colors[score];
    }

    // ── 3. Confirm password match indicator ─────────────────────────────────
    const confirmInput = document.getElementById('confirmPassword');
    if (confirmInput && pwdInput) {
        confirmInput.addEventListener('input', function () {
            if (this.value === pwdInput.value) {
                this.style.borderColor = '#27AE60';
            } else {
                this.style.borderColor = '#C0392B';
            }
        });
    }

    // ── 4. Form input highlight on focus ────────────────────────────────────
    document.querySelectorAll('.form-input').forEach(function (input) {
        // Clear any error border when user starts typing
        input.addEventListener('input', function () {
            this.classList.remove('is-error');
        });
    });

    // ── 5. Auto-dismiss alerts after 6 seconds ───────────────────────────────
    const alerts = document.querySelectorAll('.alert.auto-dismiss');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity    = '0';
            setTimeout(function () { alert.remove(); }, 500);
        }, 6000);
    });

    // ── 6. Confirm delete actions ────────────────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const msg = this.dataset.confirm || 'Are you sure?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    // ── 7. Toggle password visibility ───────────────────────────────────────
    document.querySelectorAll('.pwd-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.dataset.target;
            const input    = document.getElementById(targetId);
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = 'Hide';
            } else {
                input.type = 'password';
                this.textContent = 'Show';
            }
        });
    });

    // ── 8. Admin: highlight pending users row ───────────────────────────────
    document.querySelectorAll('tr[data-status="pending"]').forEach(function (row) {
        row.style.background = '#FEF3E2';
    });

    // ── 9. Role selector pills (register.php) ───────────────────────────────
    window.setRole = function (el, role) {
        document.querySelectorAll('[data-role]').forEach(function (b) {
            b.classList.remove('active');
        });
        el.classList.add('active');
        var hidden = document.getElementById('userRole');
        if (hidden) hidden.value = role;
    };

    // ── 10. Category chip filter (dashboard.php) ─────────────────────────────
    window.filterCat = function (el, category) {
        document.querySelectorAll('.cat-chip').forEach(function (chip) {
            chip.classList.remove('active');
        });
        el.classList.add('active');
        // Visual-only filter: hide cards that don't match (or show all)
        var cards = document.querySelectorAll('.product-card');
        cards.forEach(function (card) {
            if (category === 'all') {
                card.style.display = '';
            } else {
                var cat = (card.dataset.category || '').toLowerCase();
                card.style.display = (cat === category) ? '' : 'none';
            }
        });
    };

    // ── 11. Wishlist heart toggle (dashboard.php) ────────────────────────────
    document.querySelectorAll('.wishlist-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.toggle('liked');
        });
    });

});
