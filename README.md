# PrimeEstates

A real estate listing site — property submissions with admin
approval, favorites, contact form, search, and a full auth system.
Built as a PHP + MySQL + vanilla JS project on XAMPP.

## Stack

- **Backend:** PHP + MySQLi
- **Database:** MySQL (`primeestates`)
- **Frontend:** Vanilla JS/CSS, no framework
- **Local environment:** XAMPP (Apache + MySQL + PHP)

## Folder structure

```
PrimeEstates/
  index.php              -> homepage
  admin.php              -> admin dashboard (reached by direct URL only, no nav link)
  resetPassword.php      -> password reset landing page (opened from emailed link)
  css/
    main22.css            -> all site styles
    admin.css             -> admin dashboard styles
  js/
    auth.js               -> register/login/logout, My Properties, Favorites modal
    addProperty.js         -> "Add Property" form flow
    contact.js             -> contact form submission
    favorites.js           -> heart icon click handling
    admin.js               -> admin dashboard (properties + messages tabs)
    loadingState.js         -> shared button spinner helper
    toast.js                -> shared toast notification helper
    escapeHtml.js            -> shared HTML-escaping helper (XSS protection)
    search.js, propertyPagination.js, propertyModal.js, propertyToggle.js,
    animations.js, smoothScroll.js, heroSlider.js, mobileMenu.js, main.js
  php/
    config.php             -> DB connection + session start + environment flag
    rateLimiter.php          -> shared rate-limit helper (login/register/reset)
    simpleMailer.php         -> minimal SMTP client (no external library)
    mailConfig.php            -> Gmail SMTP credentials (fill in your own)
    login.php, register.php, logout.php, checkSession.php
    requestPasswordReset.php, resetPasswordSubmit.php
    addProperty.php, myProperties.php
    adminProperties.php, updatePropertyStatus.php, adminMessages.php
    contact.php
    toggleFavorite.php, myFavorites.php
  uploads/
    properties/            -> uploaded property images (auto-created)
```

## Local setup (XAMPP)

1. Place this folder inside `htdocs` so it's served at
   `http://localhost/PrimeEstates/`.
2. Start Apache and MySQL in the XAMPP control panel.
3. In phpMyAdmin, create a database called `primeestates`.
4. Run every `.sql` file in this project, in this order:
   - `properties_table.sql`
   - `messages_table.sql`
   - `favorites_table.sql`
   - `rate_limits_table.sql`
   - `password_resets_table.sql`
   - `seed_demo_properties.sql` (optional — seeds the original 8 demo listings as real approved properties)
5. Fill in `php/mailConfig.php` with a real Gmail address + App
   Password (see below) so password reset emails can actually send.
6. Open `http://localhost/PrimeEstates/` and register an account.

## Making an account admin

There's no UI for this — it's intentional, so random users can't
grant themselves admin. In phpMyAdmin:

1. Go to the `users` table.
2. Find your account's row, edit the `role` column from `user` to `admin`.
3. **Log out and log back in** on the site — the session won't pick
   up the new role until you do.
4. Visit `http://localhost/PrimeEstates/admin.php` directly.

## Setting up Gmail SMTP (for password reset emails)

1. Enable 2-Step Verification on the Gmail account you want to send from
   (myaccount.google.com → Security).
2. Generate an App Password at myaccount.google.com/apppasswords.
3. Put that Gmail address and the 16-character App Password into
   `php/mailConfig.php` — **not** your normal Gmail login password.

## Key design decisions worth remembering

- **Properties require admin approval.** Submitting a property always
  inserts it with `status = 'pending'`; it only appears on the public
  site once an admin approves it via the dashboard.
- **Demo cards are real database rows**, not hardcoded HTML. The
  original 8 example listings were seeded into the `properties` table
  (see `seed_demo_properties.sql`) so every card — demo or real — goes
  through the same rendering path and supports favoriting.
- **Rate limiting is IP-based**, tracked in the `rate_limits` table:
  5 failed logins / 15 min, 3 registrations / hour, 3 password reset
  requests / hour.
- **Password reset tokens expire in 1 hour** and are invalidated the
  moment any one of them is successfully used (so an old leaked email
  link can't be reused after a legitimate reset).

## Before deploying anywhere public

See `DEPLOYMENT_CHECKLIST.md` — covers environment config, HTTPS,
file upload safety, and a pre-launch smoke test.
